<?php
/*
 * (c) NETZKOLLEKTIV GmbH <kontakt@netzkollektiv.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Netzkollektiv\EasyCredit\Plugin;

use Magento\Catalog\Helper\Data as DataHelper;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\View\Element\Template\Context;
use Magento\Store\Model\ScopeInterface;

abstract class AddWidgetAbstract
{
    protected DataHelper $taxHelper;

    protected ScopeConfigInterface $scopeConfig;

    protected bool $isActive = false;

    public function __construct(
        DataHelper $taxHelper,
        Context $context
    ) {
        $this->taxHelper = $taxHelper;
        $this->scopeConfig = $context->getScopeConfig();

        $this->isActive = (bool) $this->scopeConfig->getValue(
            'payment/easycredit/marketing/widget/widget_listing_enabled',
            ScopeInterface::SCOPE_STORE
        );
    }
}
