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

class ReturnAction extends AbstractController
{
    private CartRepositoryInterface $quoteRepository;

    private EasyCreditHelper $easyCreditHelper;

    private Logger $logger;

    public function __construct(
        Context $context,
        Session $checkoutSession,
        Url $customerUrl,
        CartRepositoryInterface $quoteRepository,
        EasyCreditHelper $easyCreditHelper,
        Logger $logger
    ) {
        $this->quoteRepository = $quoteRepository;
        $this->easyCreditHelper = $easyCreditHelper;
        $this->logger = $logger;

        parent::__construct($context, $checkoutSession, $customerUrl);
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

            $this->easyCreditHelper->getCheckout()->loadTransaction();

            if (!$this->easyCreditHelper->getCheckout()->isApproved()) {
                throw new TransactionNotApprovedException(__('transaction not approved'));
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
}
