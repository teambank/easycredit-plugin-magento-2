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
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Email\Sender\OrderSender;
use Magento\Sales\Model\OrderFactory;
use Magento\Sales\Model\OrderRepository;
use Magento\Store\Model\ScopeInterface;

use Netzkollektiv\EasyCredit\Helper\Data as EasyCreditHelper;
use Netzkollektiv\EasyCredit\Logger\Logger;
use Netzkollektiv\EasyCredit\Model\Payment;
use Teambank\RatenkaufByEasyCreditApiV3\Model\TransactionInformation;

class Authorize extends AbstractController
{
    private OrderFactory $orderFactory;

    private ScopeConfigInterface $scopeConfig;

    private EasyCreditHelper $easyCreditHelper;

    private OrderRepository $orderRepository;

    private OrderSender $orderSender;

    private Logger $logger;

    public function __construct(
        Context $context,
        Session $checkoutSession,
        Url $customerUrl,
        OrderFactory $orderFactory,
        ScopeConfigInterface $scopeConfig,
        EasyCreditHelper $easyCreditHelper,
        OrderRepository $orderRepository,
        OrderSender $orderSender,
        Logger $logger
    ) {
        $this->orderFactory = $orderFactory;
        $this->scopeConfig = $scopeConfig;
        $this->easyCreditHelper = $easyCreditHelper;
        $this->orderSender = $orderSender;
        $this->orderRepository = $orderRepository;

        $this->logger = $logger;

        parent::__construct($context, $checkoutSession, $customerUrl);
    }

    /**
     * Dispatch request
     * @return void
     */
    public function execute()
    {
        $secToken = $this->getRequest()->getParam('secToken');
        $txId = $this->getRequest()->getParam('transactionId');
        $incrementId = $this->getRequest()->getParam('orderId');

        if (! $txId) {
            throw new \Exception('no transaction ID provided');
        }

        $order = $this->orderFactory->create()->loadByIncrementId($incrementId);
        if ($order->getState() != Order::STATE_PAYMENT_REVIEW) {
            throw new \Exception('order status not valid for authorization');
        }

        $payment = $order->getPayment();
        if (! isset($payment->getAdditionalInformation()['sec_token'])
            && $secToken !== $payment->getAdditionalInformation()['sec_token']
        ) {
            throw new \Exception('secToken not valid');
        }

        $token = $payment->getAdditionalInformation()['token'] ?? null;
        $tx = $this->easyCreditHelper->getCheckout()->loadTransaction($token);

        if ($tx->getStatus() !== TransactionInformation::STATUS_AUTHORIZED) {
            throw new \Exception('payment status of transaction not updated as transaction status is not AUTHORIZED');
        }

        $payment->setParentTransactionId($txId)
            ->setTransactionId($txId . '-authorize')
            ->setIsTransactionClosed(false)
            ->authorize(
                true,
                $payment->getBaseAmountOrdered()
            );

        $this->setNewOrderState($order);

        $this->orderRepository->save($order);

        try {
            $this->orderSender->send($order);
        } catch (\Exception $exception) {
            $this->logger->critical($exception);
        }
    }

    private function setNewOrderState($order): void
    {
        if (! $order instanceof Order) {
            return;
        }

        $paymentMethod = $order->getPayment()->getMethod();
        if ($paymentMethod !== Payment::CODE) {
            return;
        }

        $newOrderState = $this->scopeConfig->getValue('payment/easycredit/order_status', ScopeInterface::SCOPE_STORE);

        if (empty($newOrderState)) {
            $newOrderState = Order::STATE_PROCESSING;
        }

        $order->setState($newOrderState)
            ->setStatus($newOrderState);
    }
}
