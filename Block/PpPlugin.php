<?php
/*
 * (c) NETZKOLLEKTIV GmbH <kontakt@netzkollektiv.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Netzkollektiv\EasyCredit\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Checkout\Model\Session as CheckoutSession;

class PpPlugin extends Template
{

    private ScopeConfigInterface $scopeConfig;

    private CheckoutSession $checkoutSession;

    public function __construct(
        Context $context,
        CheckoutSession $checkoutSession,
        array $data = []
    ) {
        $this->checkoutSession = $checkoutSession;
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

    public function getGrandTotal(): float
    {
        $totals = $this->checkoutSession->getQuote()->getTotals();
        return $totals['grand_total']->getValue();
    }
}
