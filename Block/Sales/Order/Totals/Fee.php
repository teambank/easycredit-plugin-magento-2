<?php
/*
 * (c) NETZKOLLEKTIV GmbH <kontakt@netzkollektiv.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Netzkollektiv\EasyCredit\Block\Sales\Order\Totals;

use Magento\Framework\DataObject;
use Magento\Framework\DataObjectFactory;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Sales\Block\Adminhtml\Order\Totals;

class Fee extends Template
{
    protected DataObjectFactory $dataObjectFactory;

    /**
     * @var string
     */
    private const AFTER = 'subtotal';

    public function __construct(
        Context $context,
        DataObjectFactory $dataObjectFactory,
        array $data = []
    ) {
        $this->dataObjectFactory = $dataObjectFactory;
        parent::__construct(
            $context,
            $data
        );
    }

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
