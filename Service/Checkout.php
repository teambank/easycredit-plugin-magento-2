<?php
/*
 * (c) NETZKOLLEKTIV GmbH <kontakt@netzkollektiv.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Netzkollektiv\EasyCredit\Service;

use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Framework\Webapi\Exception as WebapiException;
use Magento\Framework\Exception\LocalizedException;

use Netzkollektiv\EasyCredit\Api\CheckoutInterface;
use Netzkollektiv\EasyCredit\Api\Data\CheckoutDataInterface;
use Netzkollektiv\EasyCredit\BackendApi\QuoteBuilder;
use Netzkollektiv\EasyCredit\Helper\Data as EasyCreditHelper;
use Netzkollektiv\EasyCredit\Logger\Logger;

use Teambank\RatenkaufByEasyCreditApiV3\ApiException;

class Checkout implements CheckoutInterface
{
    /**
     * @var CartRepositoryInterface
     */
    private $quoteRepository;

    /**
     * @var CheckoutSession
     */
    private $checkoutSession;

    /**
     * @var QuoteBuilder
     */
    private $easyCreditQuoteBuilder;

    /**
     * @var EasyCreditHelper
     */
    private $easyCreditHelper;

    /**
     * @var CheckoutDataInterface
     */
    private $checkoutData;

    /**
     * @var Logger
     */
    private $logger;

    public function __construct(
        CartRepositoryInterface $quoteRepository,
        CheckoutSession $checkoutSession,
        EasyCreditHelper $easyCreditHelper,
        CheckoutDataInterface $checkoutData,
        QuoteBuilder $easyCreditQuoteBuilder,
        Logger $logger
    ) {
        $this->quoteRepository = $quoteRepository;
        $this->checkoutSession = $checkoutSession;
        $this->easyCreditQuoteBuilder = $easyCreditQuoteBuilder;
        $this->easyCreditHelper = $easyCreditHelper;
        $this->checkoutData = $checkoutData;
        $this->logger = $logger;
    }

    /**
     * @api
     * @param  string $cartId
     * @return \Netzkollektiv\EasyCredit\Api\Data\CheckoutDataInterface
     */
    public function getCheckoutData($cartId)
    {
        try {
            $ecQuote = $this->easyCreditQuoteBuilder->build();
            $this->easyCreditHelper->getCheckout()->isAvailable(
                $ecQuote
            );
        } catch (\Exception $e) {
            $this->checkoutData->setErrorMessage($e->getMessage());
        }

        return $this->checkoutData;
    }

    /**
     * @throws LocalizedException
     */
    private function _validateQuote()
    {
        $quote = $this->checkoutSession->getQuote();

        if (!$quote->hasItems() || $quote->getHasError()) {
            throw new LocalizedException(__('Unable to initialize easyCredit Payment.'));
        }
    }

    /**
     * @api
     * @param  string $cartId
     * @return \Netzkollektiv\EasyCredit\Api\Data\CheckoutDataInterface
     */
    public function start($cartId)
    {
        try {
            try {
                $this->_validateQuote();

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
            } catch (ApiException $e) {
                $response = json_decode((string) $e->getResponseBody());
                if ($response === null || !isset($response->violations)) {
                    throw new \Exception('violations could not be parsed');
                }
                $messages = [];
                foreach ($response->violations as $violation) {
                    $messages[] = $violation->message;
                }

                throw new WebapiException(
                    __(implode(' ', $messages)), 
                    0, 
                    WebapiException::HTTP_FORBIDDEN
                );
            }
        } catch (WebapiException $e) {
            throw $e;
        } catch (\Throwable $e) {
            $this->logger->error($e);
            throw new WebapiException(
                __('Es ist ein Fehler aufgetreten. Leider steht Ihnen easyCredit-Ratenkauf derzeit nicht zur Verfügung.'), 
                0, 
                WebapiException::HTTP_FORBIDDEN
            );
        }
        return $this->checkoutData;
    }
}
