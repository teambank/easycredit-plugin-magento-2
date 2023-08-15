<?php
/*
 * (c) NETZKOLLEKTIV GmbH <kontakt@netzkollektiv.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Netzkollektiv\EasyCredit\Service;

use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Webapi\Exception as WebapiException;
use Magento\Quote\Api\CartRepositoryInterface;

use Netzkollektiv\EasyCredit\Api\CheckoutInterface;
use Netzkollektiv\EasyCredit\Api\Data\CheckoutDataInterface;
use Netzkollektiv\EasyCredit\BackendApi\QuoteBuilder;
use Netzkollektiv\EasyCredit\BackendApi\StorageFactory;
use Netzkollektiv\EasyCredit\Helper\Data as EasyCreditHelper;
use Netzkollektiv\EasyCredit\Logger\Logger;

use Teambank\RatenkaufByEasyCreditApiV3\ApiException;

class Checkout implements CheckoutInterface
{
    public StorageFactory $storageFactory;

    private CartRepositoryInterface $quoteRepository;

    private CheckoutSession $checkoutSession;

    private QuoteBuilder $easyCreditQuoteBuilder;

    private EasyCreditHelper $easyCreditHelper;

    private CheckoutDataInterface $checkoutData;

    private Logger $logger;

    public function __construct(CartRepositoryInterface $quoteRepository, CheckoutSession $checkoutSession, EasyCreditHelper $easyCreditHelper, CheckoutDataInterface $checkoutData, QuoteBuilder $easyCreditQuoteBuilder, Logger $logger, StorageFactory $storageFactory)
    {
        $this->quoteRepository = $quoteRepository;
        $this->checkoutSession = $checkoutSession;
        $this->easyCreditQuoteBuilder = $easyCreditQuoteBuilder;
        $this->easyCreditHelper = $easyCreditHelper;
        $this->checkoutData = $checkoutData;
        $this->logger = $logger;
        $this->storageFactory = $storageFactory;
    }

    /**
     * @api
     * @param  string $cartId
     */
    public function getCheckoutData($cartId): CheckoutDataInterface
    {
        try {
            $this->getStorage()->set('express', false);
            $ecQuote = $this->easyCreditQuoteBuilder->build();
            $this->easyCreditHelper->getCheckout()->isAvailable(
                $ecQuote
            );
        } catch (\Exception $exception) {
            $this->checkoutData->setErrorMessage($exception->getMessage());
        }

        return $this->checkoutData;
    }

    /**
     * @throws LocalizedException
     */
    private function _validateQuote(): void
    {
        $quote = $this->checkoutSession->getQuote();

        if (! $quote->hasItems() || $quote->getHasError()) {
            throw new LocalizedException(__('Unable to initialize easyCredit Payment.'));
        }
    }

    private function getStorage()
    {
        return $this->storageFactory->create(
            [
                'payment' => $this->checkoutSession->getQuote()->getPayment(),
            ]
        );
    }

    /**
     * @api
     * @param  string $cartId
     * @param  boolean $express
     */
    public function start($cartId, $express = false): CheckoutDataInterface
    {
        try {
            try {
                $this->_validateQuote();

                if ($express) {
                    $this->getStorage()->clear();
                    $this->getStorage()->set('express', true);
                    $this->prepareExpressCheckout();
                }

                $ecQuote = $this->easyCreditQuoteBuilder->build();
                $this->easyCreditHelper->getCheckout()->start(
                    $ecQuote
                );

                $quote = $this->checkoutSession->getQuote();
                $quote->getPayment()->save(); // @phpstan-ignore-line
                $quote->collectTotals();
                $this->quoteRepository->save($quote);

                if ($url = $this->easyCreditHelper->getCheckout()->getRedirectUrl()) {
                    $this->checkoutData->setRedirectUrl($url);
                }
            } catch (ApiException $apiException) {
                $response = json_decode((string) $apiException->getResponseBody(), null, 512, JSON_THROW_ON_ERROR);
                if ($response === null || ! isset($response->violations)) {
                    throw new \Exception('violations could not be parsed', $apiException->getCode(), $apiException);
                }

                $messages = [];
                foreach ($response->violations as $violation) {
                    $messages[] = $violation->messageDE ?? implode(' ', [$violation->field, $violation->message]);
                }

                throw new WebapiException(
                    __(implode(' ', $messages)),
                    0,
                    WebapiException::HTTP_FORBIDDEN
                );
            }
        } catch (WebapiException $webapiException) {
            throw $webapiException;
        } catch (\Throwable $throwable) {
            $this->logger->error($throwable);
            throw new WebapiException(
                __('Es ist ein Fehler aufgetreten. Leider steht Ihnen easyCredit-Ratenkauf derzeit nicht zur Verfügung.'),
                0,
                WebapiException::HTTP_FORBIDDEN
            );
        }

        return $this->checkoutData;
    }

    protected function prepareExpressCheckout()
    {
        $quote = $this->checkoutSession->getQuote();
        $shippingAddress = $quote->getShippingAddress();

        if ($shippingAddress->getCountryId() === null) {
            $shippingAddress->setCountryId('DE');
        }

        $shippingAddress->setCollectShippingRates(true)->collectShippingRates();
        $shippingMethod = current($shippingAddress->getAllShippingRates());

        if ($shippingMethod) {
            $shippingAddress->setShippingMethod($shippingMethod->getCode());
            $shippingAddress->collectShippingRates();
        }

        $this->quoteRepository->save($quote);
    }
}
