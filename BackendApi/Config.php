<?php
/*
 * (c) NETZKOLLEKTIV GmbH <kontakt@netzkollektiv.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Netzkollektiv\EasyCredit\BackendApi;

use Magento\Store\Model\ScopeInterface;

use Netzkollektiv\EasyCredit\Exception\InvalidSettingsException;

class Config extends \Netzkollektiv\EasyCreditApi\Config
{
    protected $scopeConfig;

    protected $_apiKey;

    protected $_apiToken;

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        $webshopApiKeyPath,
        $webshopApiTokenPath
    ) {
        $this->scopeConfig = $scopeConfig;

        $this->_apiKey = $scopeConfig->getValue($webshopApiKeyPath, ScopeInterface::SCOPE_STORE);
        $this->_apiToken = $scopeConfig->getValue($webshopApiTokenPath, ScopeInterface::SCOPE_STORE);
    }

    public function getWebshopId()
    {
        if (!isset($this->_apiKey) || empty($this->_apiKey)) {
            throw new InvalidSettingsException('api key not configured');
        }
        return $this->_apiKey;
    }

    public function getWebshopToken()
    {
        if (!isset($this->_apiToken) || empty($this->_apiToken)) {
            throw new InvalidSettingsException('api token not configured');
        }
        return $this->_apiToken;
    }
}
