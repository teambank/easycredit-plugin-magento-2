<?php
/*
 * (c) NETZKOLLEKTIV GmbH <kontakt@netzkollektiv.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Netzkollektiv\EasyCredit\Block;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\View\Element\Template\Context;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Store\Model\ScopeInterface;
use Netzkollektiv\EasyCredit\Helper\Data as DataHelper;

class Form extends \Magento\Payment\Block\Form
{

    private ScopeConfigInterface $scopeConfig;

    public function __construct(
        Context $context,
        array $data = []
    ) {
        $this->scopeConfig = $context->getScopeConfig();
        parent::__construct(
            $context,
            $data
        );
        $this->setTemplate('easycredit/form.phtml');
    }

    public function getStoreName()
    {
        $name = $this->getMethod()->getConfigData('store_name');
        $name = trim($name);
        if ($name !== '') {
            return $name;
        }

        return $this->scopeConfig->getValue(
            'general/store_information/name',
            ScopeInterface::SCOPE_STORE
        );
    }
}
