<?php
/*
 * (c) NETZKOLLEKTIV GmbH <kontakt@netzkollektiv.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Netzkollektiv\EasyCredit\Block\Adminhtml\System\Config;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

class ApiWizard extends Field
{
    /**
     * Path to block template
     * @var string
     */
    public const WIZARD_TEMPLATE = 'system/config/api_wizard.phtml';

    /**
     * @var string
     */
    public const REST_INTERNAL_VERIFY_CREDENTIALS = 'rest/V1/easycredit/verify/credentials';

    /**
     * @var string
     */
    public const REST_INTERNAL_VERIFY_CREDENTIALS_METHOD = 'get';

    /**
     * Set template to itself
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        if (! $this->getTemplate()) {
            $this->setTemplate(self::WIZARD_TEMPLATE);
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
     * Get the button and scripts contents
     *
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        $originalData = $element->getOriginalData();
        $this->addData(
            [
                'service_url' => $this->getBaseUrl() . self::REST_INTERNAL_VERIFY_CREDENTIALS,
                'service_method' => self::REST_INTERNAL_VERIFY_CREDENTIALS_METHOD,
                'button_label' => __($originalData['button_label']),
                'html_id' => $element->getHtmlId(),
                'api_key_selector' => $originalData['api_key_selector'],
                'api_token_selector' => $originalData['api_token_selector'],
                'api_signature_selector' => $originalData['api_signature_selector'],
            ]
        );
        return $this->_toHtml();
    }
}
