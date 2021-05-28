<?php
/*
 * (c) NETZKOLLEKTIV GmbH <kontakt@netzkollektiv.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Netzkollektiv\EasyCredit\Controller\Checkout;

use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\NotFoundException;

use Netzkollektiv\EasyCredit\Exception\TransactionNotApprovedException;

class ReturnAction extends AbstractController
{

    /**
     * @var CartRepositoryInterface
     */
    private $quoteRepository;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Customer\Model\Url $customerUrl,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Netzkollektiv\EasyCredit\Helper\Data $easyCreditHelper,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->quoteRepository = $quoteRepository;
        $this->easyCreditCheckout = $easyCreditHelper->getCheckout();
        $this->logger = $logger;

        parent::__construct($context, $checkoutSession, $customerUrl);
    }

    /**
     * Dispatch request
     *
     * @return ResultInterface|ResponseInterface
     * @throws NotFoundException
     */
    public function execute()
    {
        try {
            $this->_validateQuote();

            if (!$this->easyCreditCheckout->isApproved()) {
                throw new TransactionNotApprovedException(__('transaction not approved'));
            }

            $this->easyCreditCheckout->loadFinancingInformation();

            $quote = $this->checkoutSession->getQuote();
            $payment = $quote->getPayment();
            $paymentAdditionalInformation = $payment->getAdditionalInformation();

            $payment->save();
            $quote->setTotalsCollectedFlag(false);
            $quote->collectTotals();

            $payment->setMethod(\Netzkollektiv\EasyCredit\Model\Payment::CODE);
            $payment->setAdditionalInformation($paymentAdditionalInformation);

            $this->quoteRepository->save($quote);

            $this->_redirect('easycredit/checkout/review');
            return;
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('Unable to validate easyCredit Payment. (Return)'));
            $this->logger->critical($e);
        }
        $this->_redirect('checkout/cart');
    }

    /**
     * Returns action name which requires redirect
     * @return string|null
     */
    public function getRedirectActionName()
    {
        return 'return';
    }
}
