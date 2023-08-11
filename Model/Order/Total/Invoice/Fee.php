<?php
/*
 * (c) NETZKOLLEKTIV GmbH <kontakt@netzkollektiv.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Netzkollektiv\EasyCredit\Model\Order\Total\Invoice;

use Magento\Sales\Model\Order\Invoice;
use Magento\Sales\Model\Order\Invoice\Total\AbstractTotal;

class Fee extends AbstractTotal
{
    public function collect(Invoice $invoice)
    {
        $order = $invoice->getOrder();

        $feeAmountLeft = $order->getEasycreditAmount() - $order->getEasycreditAmountInvoiced();
        $baseFeeAmountLeft = $order->getBaseEasycreditAmount() - $order->getBaseEasycreditAmountInvoiced();

        if (abs($baseFeeAmountLeft) < $invoice->getBaseGrandTotal()) {
            $invoice->setGrandTotal($invoice->getGrandTotal() + $feeAmountLeft);
            $invoice->setBaseGrandTotal($invoice->getBaseGrandTotal() + $baseFeeAmountLeft);
        } else {
            $feeAmountLeft = $invoice->getGrandTotal() * -1;
            $baseFeeAmountLeft = $invoice->getBaseGrandTotal() * -1;

            $invoice->setGrandTotal(0);
            $invoice->setBaseGrandTotal(0);
        }

        $invoice->setEasycreditAmount($feeAmountLeft);
        $invoice->setBaseEasycreditAmount($baseFeeAmountLeft);

        return $this;
    }
}
