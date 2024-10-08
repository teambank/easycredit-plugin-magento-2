<?php
/*
 * (c) NETZKOLLEKTIV GmbH <kontakt@netzkollektiv.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Netzkollektiv\EasyCredit\Observer;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Quote\Model\Quote;
use Magento\Sales\Model\Order;
use Magento\Store\Model\ScopeInterface;
use Netzkollektiv\EasyCredit\Helper\Payment as PaymentHelper;

class RemoveInterest implements ObserverInterface
{
    private ScopeConfigInterface $scopeConfig;

    private PaymentHelper $paymentHelper;

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        PaymentHelper $paymentHelper
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->paymentHelper = $paymentHelper;
    }

    public function execute(Observer $observer): void
    {
        $event = $observer->getEvent();

        /**
         * @var Order $order
         */
        $order = $event->getData('order');

        /**
         * @var Quote $quote
         */
        $quote = $event->getData('quote');

        if (! $this->paymentHelper->isSelected($quote->getPayment())) {
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
