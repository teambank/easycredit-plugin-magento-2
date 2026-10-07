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

        // Drop a previous in-memory amount before applying interest again.
        // collectTotals() can run twice in one request; subtracting the sticky
        // quote value would set this total to zero on the second pass.
        $quote->setEasycreditAmount(0);
        $quote->setBaseEasycreditAmount(0);

        $this->_setAmount(0);
        $this->_setBaseAmount(0);

        $amount = $quote->getPayment()
            ->getAdditionalInformation('interest_amount');
        if ($amount == null) {
            return $this;
        }

        if ($amount <= 0) {
            return $this;
        }

        $total->setTotalAmount('easycredit', $amount);
        $total->setBaseTotalAmount('easycredit', $amount);

        $total->setEasycreditAmount($amount);
        $total->setBaseEasycreditAmount($amount);

        $quote->setEasycreditAmount($amount);
        $quote->setBaseEasycreditAmount($amount);

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
