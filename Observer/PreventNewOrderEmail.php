<?php
/*
 * (c) NETZKOLLEKTIV GmbH <kontakt@netzkollektiv.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Netzkollektiv\EasyCredit\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Quote\Model\Quote;
use Magento\Sales\Model\Order;
use Netzkollektiv\EasyCredit\Model\Payment;

class PreventNewOrderEmail implements ObserverInterface
{
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
        $paymentMethod = $quote->getPayment()->getMethod();

        if ($paymentMethod !== Payment::CODE) {
            return;
        }

        $order->setCanSendNewEmailFlag(false);
    }
}
