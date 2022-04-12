<?php
/*
 * (c) NETZKOLLEKTIV GmbH <kontakt@netzkollektiv.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Netzkollektiv\EasyCredit\Block;

use Magento\Payment\Block\Info as PaymentInfo;

class Info extends PaymentInfo
{
    protected $_template = 'Netzkollektiv_EasyCredit::easycredit/info.phtml';

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Netzkollektiv\EasyCredit\Helper\Data $easyCreditHelper,
        array $data = []
    ) {
        $this->easyCreditHelper = $easyCreditHelper;
        parent::__construct($context, $data);
    }

    /**
     * Render as PDF
     * @return string
     */
    public function toPdf()
    {
        $this->setTemplate('Netzkollektiv_EasyCredit::easycredit/info/pdf/default.phtml');
        return $this->toHtml();
    }

    public function getPaymentPlan() {
        return $this->easyCreditHelper->formatPaymentPlan($this->getInfo()->getAdditionalInformation('payment_plan'));
    }
}
