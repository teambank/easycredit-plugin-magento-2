<?php
/*
 * (c) NETZKOLLEKTIV GmbH <kontakt@netzkollektiv.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Netzkollektiv\EasyCredit\Block\Sales\Order\Invoice\Totals;

use Magento\Framework\DataObject;
use Magento\Framework\View\Element\Template;
use Magento\Sales\Block\Adminhtml\Order\Invoice\Totals;

class Fee extends Template
{
    /**
     * @var string
     */
    private const AFTER = 'subtotal';

    /**
     * @return array
     */
    public function getLabelProperties()
    {
        return $this->getParentBlock()->getLabelProperties();
    }

    /**
     * @return array
     */
    public function getValueProperties()
    {
        return $this->getParentBlock()->getValueProperties();
    }

    public function initTotals()
    {
        /**
         * @var Totals $parent
         */
        $parent = $this->getParentBlock();
        $source = $parent->getSource();

        $amount = $source->getEasycreditAmount();
        $baseAmount = $source->getBaseEasycreditAmount();
        if ($amount === null) {
            return $this;
        }

        if ((float) $amount == 0) {
            return $this;
        }

        $total = new DataObject(
            [
                'code' => 'easycredit_amount',
                'value' => $amount,
                'base_value' => $baseAmount,
                'label' => __('Interest'),
            ]
        );

        $parent->addTotal($total, self::AFTER);
        return $this;
    }
}
