<?php
/*
 * (c) NETZKOLLEKTIV GmbH <kontakt@netzkollektiv.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Netzkollektiv\EasyCredit\Controller\Checkout;

use Netzkollektiv\EasyCredit\Exception\TransactionNotApprovedException;
use Netzkollektiv\EasyCredit\Helper\Data as EasyCreditHelper;
use Netzkollektiv\EasyCredit\Logger\Logger;
use Magento\Quote\Api\CartRepositoryInterface;

class ReturnAction extends AbstractController
{
    /**
     * @var CartRepositoryInterface
     */
    private $quoteRepository;

    /**
     * @var EasyCreditHelper
     */
    private $easyCreditHelper;

    /**
     * @var Logger
     */
    private $logger;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Customer\Model\Url $customerUrl,
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

            $payment->setMethod(\Netzkollektiv\EasyCredit\Model\Payment::CODE);
            $payment->setAdditionalInformation($paymentAdditionalInformation);

            $this->quoteRepository->save($quote);

            $this->_redirect('easycredit/checkout/review');
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('Unable to validate easyCredit Payment.'));
            $this->logger->critical($e);
            $this->_redirect('checkout/cart');
        }
    }

    /**
     * Returns action name which requires redirect
     *
     * @return string|null
     */
    public function getRedirectActionName()
    {
        return 'return';
    }
}
