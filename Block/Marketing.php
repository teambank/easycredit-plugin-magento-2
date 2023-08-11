<?php
/*
 * (c) NETZKOLLEKTIV GmbH <kontakt@netzkollektiv.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Netzkollektiv\EasyCredit\Block;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Store\Model\ScopeInterface;

class Marketing extends Template
{
    private ScopeConfigInterface $scopeConfig;

    public function __construct(
        Context $context,
        array $data = []
    ) {
        $this->scopeConfig = $context->getScopeConfig();
        parent::__construct($context, $data);
    }

    public function getConfigValue($key)
    {
        return $this->scopeConfig->getValue(
            'payment/easycredit/marketing/' . $key,
            ScopeInterface::SCOPE_STORE
        );
    }

    public function getBaseUrlMedia(): string
    {
        return $this->_storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA) . 'netzkollektiv/easycredit/';
    }
}
