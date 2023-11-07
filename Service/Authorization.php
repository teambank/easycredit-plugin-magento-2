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
use Netzkollektiv\EasyCredit\BackendApi\QuoteBuilder;
use Netzkollektiv\EasyCredit\Helper\Data as EasyCreditHelper;
use Netzkollektiv\EasyCredit\Model\Payment;

class Authorization
{
    private EasyCreditHelper $easyCreditHelper;

    private QuoteBuilder $easyCreditQuoteBuilder;

    private OrderRepository $orderRepository;

    private OrderSender $orderSender;

    private ScopeConfigInterface $scopeConfig;

    public function __construct(
        EasyCreditHelper $easyCreditHelper,
        QuoteBuilder $easyCreditQuoteBuilder,
        OrderRepository $orderRepository,
        OrderSender $orderSender,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->easyCreditHelper = $easyCreditHelper;
        $this->easyCreditQuoteBuilder = $easyCreditQuoteBuilder;
        $this->orderRepository = $orderRepository;
        $this->orderSender = $orderSender;
        $this->scopeConfig = $scopeConfig;
    }

    public function authorize($order): void
    {
        if ($order->getPayment()->getMethod() != Payment::CODE) {
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
