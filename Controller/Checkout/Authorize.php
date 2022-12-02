<?php
/*
 * (c) NETZKOLLEKTIV GmbH <kontakt@netzkollektiv.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Netzkollektiv\EasyCredit\Controller\Checkout;


use Magento\Sales\Api\Data\TransactionInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Payment\Transaction;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Sales\Model\OrderFactory;
use Magento\Sales\Model\OrderRepository;

use Netzkollektiv\EasyCredit\BackendApi\QuoteBuilder;
use Teambank\RatenkaufByEasyCreditApiV3\Model\TransactionInformation;
use Magento\Sales\Model\Order\Email\Sender\OrderSender;
use Netzkollektiv\EasyCredit\Logger\Logger;
use Netzkollektiv\EasyCredit\Helper\Data as EasyCreditHelper;

class Authorize extends AbstractController
{
    /**
     * @var OrderFactory
     */
    private $orderFactory;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var EasyCreditHelper
     */
    private $easyCreditHelper;

    /**
     * @var OrderRepository
     */
    private $orderRepository;

    /**
     * @var OrderSender
     */
    private $orderSender;

    /**
     * @var Logger
     */
    private $logger;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Customer\Model\Url $customerUrl,
        \Magento\Sales\Model\OrderFactory $orderFactory,
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
     *
     * @return void
     */
    public function execute()
    {
        $secToken = $this->getRequest()->getParam('secToken');
        $txId = $this->getRequest()->getParam('transactionId');
        $incrementId = $this->getRequest()->getParam('orderId');

        if (!$txId) {
            throw new \Exception('no transaction ID provided');
        }

        $order = $this->orderFactory->create()->loadByIncrementId($incrementId);
        if ($order->getState() != Order::STATE_PAYMENT_REVIEW) {
            throw new \Exception('order status not valid for authorization');
        }

        $payment = $order->getPayment();
        if (!isset($payment->getAdditionalInformation()['sec_token']) 
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
            ->setTransactionId($txId.'-authorize')
            ->setIsTransactionClosed(false)
            ->authorize(
                true,
                $payment->getBaseAmountOrdered()
            );

        $this->setNewOrderState($order);

        $this->orderRepository->save($order);

        try {
            $this->orderSender->send($order);
        } catch (\Exception $e) {
            $this->logger->critical($e);
        }
    }

    private function setNewOrderState($order)
    {
        if (! $order instanceof \Magento\Sales\Model\Order) {
            return;
        }

        $paymentMethod = $order->getPayment()->getMethod();
        if ($paymentMethod !== \Netzkollektiv\EasyCredit\Model\Payment::CODE) {
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
