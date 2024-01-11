<?php
/*
 * (c) NETZKOLLEKTIV GmbH <kontakt@netzkollektiv.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Netzkollektiv\EasyCredit\Block\Adminhtml\Order\View\Info;

use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Registry;
use Magento\Sales\Model\Order;
use Netzkollektiv\EasyCredit\Helper\Payment as PaymentHelper;

class Transaction extends Template
{
    private Registry $registry;

    private PaymentHelper $paymentHelper;

    public function __construct(
        Context $context,
        Registry $registry,
        PaymentHelper $paymentHelper,
        array $data = []
    ) {
        $this->registry = $registry;
        $this->paymentHelper = $paymentHelper;
        parent::__construct($context, $data);
    }

    /**
     * @return \Magento\Sales\Model\Order\Payment
     */
    public function getPayment()
    {
        return $this->getOrder()->getPayment();
    }

    /**
     * @return Order
     */
    public function getOrder()
    {
        return $this->registry->registry('current_order');
    }

    public function getCreatedAtFormatted(): string
    {
        $date = new \DateTime($this->getOrder()->getCreatedAt());
        return $date->format('Y-m-d');
    }

    /**
     * @return string
     */
    protected function _toHtml()
    {
        if ($this->paymentHelper->isSelected($this->getPayment())) {
            return parent::_toHtml();
        }
        return '';
    }
}
