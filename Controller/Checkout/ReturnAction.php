<?php
/*
 * (c) NETZKOLLEKTIV GmbH <kontakt@netzkollektiv.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Netzkollektiv\EasyCredit\Controller\Checkout;

use Magento\Framework\App\Action\Context;
use Magento\Checkout\Model\Session;
use Magento\Customer\Model\Url;
use Netzkollektiv\EasyCredit\Model\Payment;
use Netzkollektiv\EasyCredit\Exception\TransactionNotApprovedException;
use Netzkollektiv\EasyCredit\Helper\Data as EasyCreditHelper;
use Netzkollektiv\EasyCredit\Logger\Logger;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Checkout\Model\Session as CheckoutSession;
use Netzkollektiv\EasyCredit\BackendApi\StorageFactory;
use Netzkollektiv\EasyCredit\BackendApi\QuoteBuilder;
use Teambank\RatenkaufByEasyCreditApiV3\Model\TransactionInformation;

class ReturnAction extends AbstractController
{
    private CartRepositoryInterface $quoteRepository;

    private EasyCreditHelper $easyCreditHelper;

    private Logger $logger;

    private StorageFactory $storageFactory;

    public function __construct(
        Context $context,
        Session $checkoutSession,
        Url $customerUrl,
        CartRepositoryInterface $quoteRepository,
        EasyCreditHelper $easyCreditHelper,
        Logger $logger,
        StorageFactory $storageFactory,
    ) {
        parent::__construct($context, $checkoutSession, $customerUrl);
    }

    private function getStorage() {
        return $this->storageFactory->create(
            [
            'payment' => $this->checkoutSession->getQuote()->getPayment()
            ]
        );
    }

    /**
     * Dispatch request
     *
     * @return void
     */
    public function execute()
    {
        try {
            $this->_validateQuote();

            $checkout = $this->easyCreditHelper->getCheckout();
            $transaction = $checkout->loadTransaction();

            if (!$checkout->isApproved()) {
                throw new TransactionNotApprovedException(__('transaction not approved'));
            }

            if ($this->getStorage()->get('express')) {
                $this->importExpressCheckoutData($transaction);
                $this->getStorage()->set('express', false);
                $checkout->finalizeExpress($this->easyCreditQuoteBuilder->build());
            }

            $quote = $this->checkoutSession->getQuote();
            $payment = $quote->getPayment();
            $paymentAdditionalInformation = $payment->getAdditionalInformation();

            $payment->save(); // @phpstan-ignore-line 

            $quote->setTotalsCollectedFlag(false);
            $quote->collectTotals();

            $payment->setMethod(Payment::CODE);
            $payment->setAdditionalInformation($paymentAdditionalInformation);

            $this->quoteRepository->save($quote);

            $this->_redirect('easycredit/checkout/review');
        } catch (\Exception $exception) {
            $this->messageManager->addErrorMessage(__('Unable to validate easyCredit Payment.'));
            $this->logger->critical($exception);
            $this->_redirect('checkout/cart');
        }
    }

    /**
     * Returns action name which requires redirect
     */
    public function getRedirectActionName(): string
    {
        return 'return';
    }

    protected function importExpressCheckoutData(TransactionInformation $transaction) {
        $customer = $transaction->getTransaction()->getCustomer();
        $contact = $customer->getContact();
        $address = $transaction->getTransaction()->getOrderDetails()->getShippingAddress();

        $quote = $this->checkoutSession->getQuote();

        $address = [
            'email' => $contact->getEmail(),
            'prefix' => $customer->getGender(),
            'middlename' => null,
            'suffix' => null,
            'firstname' => $address->getFirstname(),
            'lastname' => $address->getLastname(),
            'street' => $address->getAddress(),
            'street2' => null,
            'postcode' => $address->getZip(),
            'city' => $address->getCity(),
            'country_id' => $address->getCountry(),
            'region' => null,
            'telephone' => $contact->getMobilePhoneNumber(),
        ];

        $quote->getBillingAddress()->addData($address);
        $quote->getShippingAddress()->addData($address);
    }
}
