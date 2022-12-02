<?php
/*
 * (c) NETZKOLLEKTIV GmbH <kontakt@netzkollektiv.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Netzkollektiv\EasyCredit\Block;

use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Store\Model\ScopeInterface;
use Netzkollektiv\EasyCredit\Helper\Data as DataHelper;

class Form extends \Magento\Payment\Block\Form
{

    /**
     * @var \Magento\Framework\View\LayoutInterface
     */
    private $layout;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var DataHelper $dataHelper
     */
    private $dataHelper;

    /**
     * @var CheckoutSession
     */
    private $checkoutSession;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        DataHelper $dataHelper,
        CheckoutSession $checkoutSession,
        array $data = []
    ) {
        $this->layout = $context->getLayout();
        $this->scopeConfig = $context->getScopeConfig();
        $this->dataHelper = $dataHelper;
        $this->checkoutSession = $checkoutSession;
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
        if (!empty($name)) {
            return $name;
        }

        return $this->scopeConfig->getValue(
            'general/store_information/name',
            ScopeInterface::SCOPE_STORE
        );
    }
}
