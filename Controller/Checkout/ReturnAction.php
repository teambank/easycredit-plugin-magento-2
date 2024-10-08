<?php
/*
 * (c) NETZKOLLEKTIV GmbH <kontakt@netzkollektiv.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Netzkollektiv\EasyCredit\Controller\Checkout;

use Magento\Checkout\Model\Session;
use Magento\Customer\Model\Url;
use Magento\Framework\App\Action\Context;
use Magento\Quote\Api\CartRepositoryInterface;
use Netzkollektiv\EasyCredit\BackendApi\QuoteBuilder;
use Netzkollektiv\EasyCredit\BackendApi\StorageFactory;
use Netzkollektiv\EasyCredit\Exception\TransactionNotApprovedException;
use Netzkollektiv\EasyCredit\Helper\Data as EasyCreditHelper;
use Netzkollektiv\EasyCredit\Logger\Logger;
use Netzkollektiv\EasyCredit\Helper\Payment as PaymentHelper;

use Teambank\EasyCreditApiV3\Model\TransactionInformation;

class ReturnAction extends AbstractController
{
    private CartRepositoryInterface $quoteRepository;

    private EasyCreditHelper $easyCreditHelper;

    private QuoteBuilder $easyCreditQuoteBuilder;

    private PaymentHelper $paymentHelper;

    private Logger $logger;

    private StorageFactory $storageFactory;

    public function __construct(
        Context $context,
        Session $checkoutSession,
        Url $customerUrl,
        CartRepositoryInterface $quoteRepository,
        EasyCreditHelper $easyCreditHelper,
        QuoteBuilder $easyCreditQuoteBuilder,
        PaymentHelper $paymentHelper,
        Logger $logger,
        StorageFactory $storageFactory
    ) {
        $this->quoteRepository = $quoteRepository;
        $this->easyCreditHelper = $easyCreditHelper;
        $this->easyCreditQuoteBuilder = $easyCreditQuoteBuilder;
        $this->paymentHelper = $paymentHelper;
        $this->logger = $logger;
        $this->storageFactory = $storageFactory;
        parent::__construct($context, $checkoutSession, $customerUrl);
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
     * Dispatch request
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

            $payment->setMethod($this->paymentHelper->getMethodByType($transaction->getTransaction()->getPaymentType()));
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

    protected function importExpressCheckoutData(TransactionInformation $transaction)
    {
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
