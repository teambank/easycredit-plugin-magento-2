<?php
/*
 * (c) NETZKOLLEKTIV GmbH <kontakt@netzkollektiv.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Netzkollektiv\EasyCredit\Model\Sales\Quote\Address\Total;

use Magento\Checkout\Model\Session as CheckoutSession;

class Fee extends \Magento\Quote\Model\Quote\Address\Total\AbstractTotal
{
    protected $_code = 'easycredit';

    public function collect(
        \Magento\Quote\Model\Quote $quote,
        \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment,
        \Magento\Quote\Model\Quote\Address\Total $total
    ) {
        parent::collect($quote, $shippingAssignment, $total);

        $this->clearValues($total);

        $items = $shippingAssignment->getItems();
        if (!count($items)) {
            return $this;
        }

        $this->_setAmount(0);
        $this->_setBaseAmount(0);

        $amount = $quote->getPayment()->getAdditionalInformation('interest_amount');

        if ($amount == null || $amount <= 0) {
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
     *
     * @param  \Magento\Quote\Model\Quote\Address\Total $total
     * @return void
     */
    private function clearValues(\Magento\Quote\Model\Quote\Address\Total $total)
    {
        $total->setTotalAmount('easycredit', 0);
        $total->setBaseTotalAmount('easycredit', 0);
    }

    /**
     * @param  \Magento\Quote\Model\Quote               $quote
     * @param  \Magento\Quote\Model\Quote\Address\Total $total
     * @return array|null
     */
    public function fetch(
        \Magento\Quote\Model\Quote $quote,
        \Magento\Quote\Model\Quote\Address\Total $total
    ) {
        $result = null;

        $amount = $total->getEasycreditAmount();

        if ($amount != 0) {
            $result = [
                'code' => 'easycredit',
                'title' => __('Interest'),
                'value' => $amount
            ];
        }
        return $result;
    }

    /**
     * Get Subtotal label
     *
     * @return \Magento\Framework\Phrase
     */
    public function getLabel()
    {
        return __('Interest');
    }
}
