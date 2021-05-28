<?php
/*
 * (c) NETZKOLLEKTIV GmbH <kontakt@netzkollektiv.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Netzkollektiv\EasyCredit\Controller\Checkout;

use Magento\Framework\App\ResponseInterface;

class PlaceOrder extends AbstractController
{

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $session;

    /**
     * Checkout data
     *
     * @var \Magento\Checkout\Helper\Data
     */
    protected $checkoutData;

    /**
     * @var \Magento\Quote\Api\CartManagementInterface
     */
    protected $cartManagement;

    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @var \Magento\Sales\Model\Order\Email\Sender\OrderSender $orderSender
     */
    protected $orderSender;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Customer\Model\Url $customerUrl,
        \Magento\Customer\Model\Session $customerSession,
        \Netzkollektiv\EasyCredit\Helper\Data $easyCreditHelper,
        \Netzkollektiv\EasyCredit\BackendApi\Quote $easyCreditQuote,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Sales\Model\Order\Email\Sender\OrderSender $orderSender,
        \Magento\Quote\Api\CartManagementInterface $cartManagement,
        \Magento\Checkout\Helper\Data $checkoutData,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->customerSession = $customerSession;
        $this->easyCreditCheckout = $easyCreditHelper->getCheckout();
        $this->easyCreditQuote = $easyCreditQuote;
        $this->orderRepository = $orderRepository;
        $this->orderSender = $orderSender;
        $this->cartManagement = $cartManagement;
        $this->checkoutData = $checkoutData;
        $this->logger = $logger;
        parent::__construct($context, $checkoutSession, $customerUrl);
    }

    /**
     * Dispatch request
     *
     * @return \Magento\Framework\Controller\ResultInterface|ResponseInterface
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function execute()
    {
        if (!$this->easyCreditCheckout->isInitialized()) {
            $this->messageManager->addErrorMessage(
                __('Unable to finish easyCredit Checkout. Please restart payment process.')
            );
            $this->_redirect('checkout/cart');
            return null;
        }

        if (!$this->easyCreditCheckout->verifyAddressNotChanged($this->easyCreditQuote)) {
            $this->messageManager->addErrorMessage(
                __("Unable to finish easyCredit Checkout. 
                Shipping address has been changed. Please restart payment procedure.")
            );
            $this->_redirect('checkout/cart');
            return null;
        }

        if (!$this->easyCreditCheckout->sameAddresses($this->easyCreditQuote)) {
            $this->messageManager->addErrorMessage(
                __("Unable to finish easyCredit Checkout. Shipping address and billing address are not identical. 
                Please restart payment procedure.")
            );
            $this->_redirect('checkout/cart');
            return null;
        }

        if (!$this->easyCreditCheckout->isAmountValid($this->easyCreditQuote)) {
            $this->logger->debug('Unable to finish easyCredit Checkout. 
                Amounts changed. Please restart payment procedure.');

            $this->easyCreditCheckout->clear();

            $this->messageManager->addErrorMessage(
                __('Unable to finish easyCredit Checkout. Amounts changed. Please restart payment procedure.')
            );
            $this->_redirect('checkout/cart');
            return null;
        }

        $quote = $this->checkoutSession->getQuote();

        if (!$this->customerSession->isLoggedIn()) {
            if (!$this->checkoutData->isAllowedGuestCheckout($quote)) {
                $this->messageManager->addErrorMessage(__('Guest checkout is not allowed.'));
                $this->_redirect('checkout/cart');
                return null;
            }

            $quote->setCustomerId(null)
                ->setCustomerEmail($quote->getBillingAddress()->getEmail())
                ->setCustomerIsGuest(true)
                ->setCustomerGroupId(\Magento\Customer\Model\Group::NOT_LOGGED_IN_ID);
        }

        try {
            $orderId = $this->cartManagement->placeOrder($quote->getId());
            $order = $this->orderRepository->get($orderId);
            $this->orderSender->send($order);
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addErrorMessage(__($e->getMessage()));
            $this->_redirect('easycredit/checkout/cancel');
        }

        $this->_redirect('checkout/onepage/success');

        return null;
    }
}
