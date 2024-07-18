<?php
/*
 * (c) NETZKOLLEKTIV GmbH <kontakt@netzkollektiv.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Netzkollektiv\EasyCredit\Block\Adminhtml\System\Config;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Backend\Block\Template\Context;
use Magento\Store\Model\ScopeInterface;

class BillPaymentIntro extends Field
{
    /**
     * Path to block template
     * @var string
     */
    public const BILLPAYMENT_INTRO_TEMPLATE = 'system/config/billpayment_intro.phtml';

    private ScopeConfigInterface $scopeConfig;

    public function __construct(
        Context $context,
        array $data = []
    ) {
        $this->scopeConfig = $context->getScopeConfig();

        parent::__construct($context, $data);
    }

    public function getApiKey()
    {
        return $this->scopeConfig->getValue(
            'payment/easycredit/credentials/api_key',
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Set template to itself
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        if (! $this->getTemplate()) {
            $this->setTemplate(self::BILLPAYMENT_INTRO_TEMPLATE);
        }

        parent::_prepareLayout();
        return $this;
    }

    /**
     * Unset some non-related element parameters
     *
     * @return string
     */
    public function render(AbstractElement $element)
    {
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();
        return parent::render($element);
    }

    /**
     * Get the intro and scripts contents
     *
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        $originalData = $element->getOriginalData();
        $this->addData(
            [
                'html_id' => $element->getHtmlId(),
                'api_key' => $this->getApiKey(),
            ]
        );
        return $this->_toHtml();
    }
}
