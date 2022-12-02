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

class RemoveInterest implements ObserverInterface
{

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    public function __construct(\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @param  Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        $event = $observer->getEvent();

        /**
         * @var \Magento\Sales\Model\Order $order
         */
        $order = $event->getData('order');

        /**
         * @var \Magento\Quote\Model\Quote $quote
         */
        $quote = $event->getData('quote');
        $paymentMethod = $quote->getPayment()->getMethod();

        if ($paymentMethod !== \Netzkollektiv\EasyCredit\Model\Payment::CODE) {
            return;
        }

        $removeInterest = $this->scopeConfig->getValue(
            'payment/easycredit/remove_interest',
            ScopeInterface::SCOPE_STORE
        );

        if ($removeInterest) {
            $order->setGrandTotal($order->getGrandTotal() - $quote->getShippingAddress()->getEasycreditAmount());
            $order->setBaseGrandTotal($order->getBaseGrandTotal() - $quote->getShippingAddress()->getBaseEasycreditAmount());

            $order->setTotalDue($order->getTotalDue() - $quote->getShippingAddress()->getEasycreditAmount());
            $order->setBaseTotalDue($order->getBaseTotalDue() - $quote->getShippingAddress()->getBaseEasycreditAmount());

            return;
        }

        $order->setEasycreditAmount($quote->getShippingAddress()->getEasycreditAmount());
        $order->setBaseEasycreditAmount($quote->getShippingAddress()->getBaseEasycreditAmount());
    }
}
