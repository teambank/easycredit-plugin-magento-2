<?php
/*
 * (c) NETZKOLLEKTIV GmbH <kontakt@netzkollektiv.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Netzkollektiv\EasyCredit\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Model\Order\Creditmemo;

class CreditmemoSaveAfter implements ObserverInterface
{
    public function execute(Observer $observer): void
    {
        $event = $observer->getEvent();

        /**
         * @var Creditmemo $creditmemo
         */
        $creditmemo = $event->getCreditmemo();

        if ($creditmemo->getEasycreditAmount()) {
            $order = $creditmemo->getOrder();
            $order->setEasycreditAmountRefunded(
                $order->getEasycreditAmountRefunded() + $creditmemo->getEasycreditAmount()
            );
            $order->setBaseEasycreditAmountRefunded(
                $order->getBaseEasycreditAmountRefunded() + $creditmemo->getBaseEasycreditAmount()
            );
        }
    }
}
