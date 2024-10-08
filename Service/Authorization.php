<?php
/*
 * (c) NETZKOLLEKTIV GmbH <kontakt@netzkollektiv.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Netzkollektiv\EasyCredit\Service;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Sales\Model\Order\Email\Sender\OrderSender;
use Magento\Sales\Model\OrderRepository;
use Magento\Store\Model\ScopeInterface;
use Netzkollektiv\EasyCredit\Helper\Payment as PaymentHelper;

class Authorization
{
    private OrderRepository $orderRepository;

    private OrderSender $orderSender;

    private ScopeConfigInterface $scopeConfig;

    private PaymentHelper $paymentHelper;

    public function __construct(
        OrderRepository $orderRepository,
        OrderSender $orderSender,
        ScopeConfigInterface $scopeConfig,
        PaymentHelper $paymentHelper
    ) {
        $this->orderRepository = $orderRepository;
        $this->orderSender = $orderSender;
        $this->scopeConfig = $scopeConfig;
        $this->paymentHelper = $paymentHelper;
    }

    public function authorize($order): void
    {
        if (! $this->paymentHelper->isSelected($order->getPayment())) {
            return;
        }

        $baseTotalDue = $order->getBaseTotalDue();
        $order->getPayment()->authorize(true, $baseTotalDue);

        $newOrderStatus = $this->scopeConfig->getValue('payment/easycredit/order_status', ScopeInterface::SCOPE_STORE);
        $order->addStatusHistoryComment(__('Payment authorized by easyCredit. Order status was set as configured in payment method.'), $newOrderStatus);

        $this->orderRepository->save($order);
        $this->orderSender->send($order);
    }
}
