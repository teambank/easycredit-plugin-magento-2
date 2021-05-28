<?php
/*
 * (c) NETZKOLLEKTIV GmbH <kontakt@netzkollektiv.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Netzkollektiv\EasyCredit\Model\Order\Total\Creditmemo;

use Magento\Sales\Model\Order\Creditmemo\Total\AbstractTotal;

class Fee extends AbstractTotal
{
    public function collect(\Magento\Sales\Model\Order\Creditmemo $creditmemo)
    {
        $order = $creditmemo->getOrder();

        if ($order->getEasycreditAmountInvoiced() > 0) {
            $feeAmountLeft = $order->getEasycreditAmountInvoiced() - $order->getEasycreditAmountRefunded();
            $basefeeAmountLeft = $order->getBaseEasycreditAmountInvoiced() - $order->getBaseEasycreditAmountRefunded();

            if ($basefeeAmountLeft > 0) {
                $creditmemo->setGrandTotal($creditmemo->getGrandTotal() + $feeAmountLeft);
                $creditmemo->setBaseGrandTotal($creditmemo->getBaseGrandTotal() + $basefeeAmountLeft);
                $creditmemo->setEasycreditAmount($feeAmountLeft);
                $creditmemo->setBaseEasycreditAmount($basefeeAmountLeft);
            }
        } else {
            $feeAmount = $order->getEasycreditAmountInvoiced();
            $basefeeAmount = $order->getBaseEasycreditAmountInvoiced();

            $creditmemo->setGrandTotal($creditmemo->getGrandTotal() + $feeAmount);
            $creditmemo->setBaseGrandTotal($creditmemo->getBaseGrandTotal() + $basefeeAmount);
            $creditmemo->setEasycreditAmount($feeAmount);
            $creditmemo->setBaseEasycreditAmount($basefeeAmount);
        }

        return $this;
    }
}
