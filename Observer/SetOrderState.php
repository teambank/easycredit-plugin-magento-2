<?php
/*
 * (c) NETZKOLLEKTIV GmbH <kontakt@netzkollektiv.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Netzkollektiv\EasyCredit\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Store\Model\ScopeInterface;

class SetOrderState implements ObserverInterface
{

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Sales\Model\OrderRepository
     */
    protected $orderRepository;

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Sales\Model\OrderRepository $orderRepository
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->orderRepository = $orderRepository;
    }

    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        $event = $observer->getEvent();

        /**
         * @var $order \Magento\Sales\Model\Order
         */
        $order = $event->getData('order');
        if (! $order instanceof \Magento\Sales\Model\Order) {
            return;
        }

        $paymentMethod = $order->getPayment()->getMethod();
        if ($paymentMethod !== \Netzkollektiv\EasyCredit\Model\Payment::CODE) {
            return;
        }

        $newOrderState = $this->scopeConfig->getValue('payment/easycredit/order_status', ScopeInterface::SCOPE_STORE);

        if (!empty($newOrderState)) {
            $order->setState($newOrderState)->setStatus($newOrderState);
            $this->orderRepository->save($order);
        }
    }
}
