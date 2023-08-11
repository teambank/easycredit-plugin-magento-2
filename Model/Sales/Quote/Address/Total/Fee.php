<?php
/*
 * (c) NETZKOLLEKTIV GmbH <kontakt@netzkollektiv.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Netzkollektiv\EasyCredit\Model\Sales\Quote\Address\Total;

use Magento\Framework\Phrase;
use Magento\Quote\Api\Data\ShippingAssignmentInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address\Total;
use Magento\Quote\Model\Quote\Address\Total\AbstractTotal;

class Fee extends AbstractTotal
{
    protected $_code = 'easycredit';

    public function collect(
        Quote $quote,
        ShippingAssignmentInterface $shippingAssignment,
        Total $total
    ) {
        parent::collect($quote, $shippingAssignment, $total);

        $this->clearValues($total);

        $items = $shippingAssignment->getItems();
        if ($items === []) {
            return $this;
        }

        $this->_setAmount(0);
        $this->_setBaseAmount(0);

        $amount = $quote->getPayment()->getAdditionalInformation('interest_amount');
        if ($amount == null) {
            return $this;
        }

        if ($amount <= 0) {
            return $this;
        }

        $exist_amount = $quote->getEasycreditAmount();

        $balance = $amount - $exist_amount;

        $total->setTotalAmount('easycredit', $balance);
        $total->setBaseTotalAmount('easycredit', $balance);

        $total->setEasycreditAmount($balance);
        $total->setBaseEasycreditAmount($balance);

        $quote->setEasycreditAmount($balance);
        $quote->setBaseEasycreditAmount($balance);

        return $this;
    }

    /**
     * Clear easycredit related total values in address
     */
    private function clearValues(Total $total): void
    {
        $total->setTotalAmount('easycredit', 0);
        $total->setBaseTotalAmount('easycredit', 0);
    }

    /**
     * @return array|null
     */
    public function fetch(
        Quote $quote,
        Total $total
    ) {
        $amount = $total->getEasycreditAmount();

        if ($amount != 0) {
            return [
                'code' => 'easycredit',
                'title' => __('Interest'),
                'value' => $amount,
            ];
        }

        return null;
    }

    /**
     * Get Subtotal label
     *
     * @return Phrase
     */
    public function getLabel()
    {
        return __('Interest');
    }
}
