<?php
/*
 * (c) NETZKOLLEKTIV GmbH <kontakt@netzkollektiv.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Netzkollektiv\EasyCredit\Block\Sales\Order\Invoice\Totals;

class Fee extends \Magento\Framework\View\Element\Template
{

    /**
     * @var \Magento\Framework\DataObjectFactory
     */
    private $dataObjectFactory;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\DataObjectFactory $dataObjectFactory,
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
         * @var \Magento\Sales\Block\Adminhtml\Order\Invoice\Totals $parent
         */
        $parent = $this->getParentBlock();
        $source = $parent->getSource();

        $amount = $source->getEasycreditAmount();
        $baseAmount = $source->getBaseEasycreditAmount();

        if ($amount === null || (float) $amount == 0) {
            return $this;
        }

        $total = new \Magento\Framework\DataObject(
            [
            'code'  => 'easycredit_amount',
            'value' => $amount,
            'base_value' => $baseAmount,
            'label' => __('Interest')
            ]
        );
        $after = 'subtotal';

        $parent->addTotal($total, $after);
        return $this;
    }
}
