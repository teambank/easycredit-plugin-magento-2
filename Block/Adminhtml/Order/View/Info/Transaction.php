<?php
/*
 * (c) NETZKOLLEKTIV GmbH <kontakt@netzkollektiv.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Netzkollektiv\EasyCredit\Block\Adminhtml\Order\View\Info;

use Magento\Backend\Block\Template;
use Magento\Framework\Registry;
use Magento\Backend\Block\Template\Context;
use Magento\Sales\Model\Order;
use Netzkollektiv\EasyCredit\Model\Payment as EasyCreditPayment;

class Transaction extends Template
{
    private Registry $registry;

    public function __construct(
        Context $context,
        Registry $registry,
        array $data = []
    ) {
        $this->registry = $registry;
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
        return ($this->getPayment()->getMethod() === EasyCreditPayment::CODE) ? parent::_toHtml() : '';
    }
}
