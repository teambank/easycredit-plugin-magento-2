<?php
/*
 * (c) NETZKOLLEKTIV GmbH <kontakt@netzkollektiv.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Netzkollektiv\EasyCredit\BackendApi;

class Quote implements \Netzkollektiv\EasyCreditApi\Rest\QuoteInterface
{
    protected $_quote;
    protected $_customerSession;
    protected $_checkoutSession;
    protected $_salesOrderCollection;
    protected $_storeManager;
    protected $_categoryResource;
    protected $_productMetadata;
    protected $_moduleResource;
    protected $_easyCreditHelper;

    public function __construct(
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Sales\Model\ResourceModel\Order\Collection $salesOrderCollection,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\ResourceModel\Category $categoryResource,
        \Magento\Framework\App\ProductMetadataInterface $productMetadata,
        \Magento\Framework\Module\ResourceInterface $moduleResource,
        \Netzkollektiv\EasyCredit\Helper\Data $easyCreditHelper,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->_quote = $checkoutSession->getQuote();
        $this->_customerSession = $customerSession;
        $this->_checkoutSession = $checkoutSession;
        $this->_salesOrderCollection = $salesOrderCollection;
        $this->_storeManager = $storeManager;
        $this->_categoryResource = $categoryResource;
        $this->_productMetadata = $productMetadata;
        $this->_moduleResource = $moduleResource;
        $this->_easyCreditHelper = $easyCreditHelper;
        $this->scopeConfig = $scopeConfig;
    }

    public function getId()
    {
        return $this->_quote->getId();
    }

    public function getShippingMethod()
    {
        if ($this->_quote->getShippingAddress()) {
            return $this->_quote->getShippingAddress()->getShippingMethod();
        }
    }

    public function getIsClickAndCollect()
    {
        if ($this->_quote->getShippingAddress() && $shippingMethod = $this->_quote->getShippingAddress()->getShippingMethod()) {
            return $shippingMethod === $this->scopeConfig->getValue('payment/easycredit/clickandcollect/shipping_method', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        }
        return false;
    }

    public function getGrandTotal()
    {
        return $this->_quote->getGrandTotal();
    }

    public function getBillingAddress()
    {
        return new Quote\Address($this->_quote->getBillingAddress());
    }

    public function getShippingAddress()
    {
        return new Quote\ShippingAddress($this->_quote->getShippingAddress());
    }

    public function getCustomer()
    {
        return new Quote\Customer(
            $this->_customerSession,
            $this->_salesOrderCollection,
            $this->_quote->getCustomer(),
            $this->_quote->getBillingAddress(),
            $this->_quote->getShippingAddress(),
            $this->_easyCreditHelper
        );
    }

    public function getItems()
    {
        return $this->_getItems(
            $this->_quote->getAllVisibleItems()
        );
    }

    protected function _getItems($items)
    {
        $_items = [];
        foreach ($items as $item) {
            $_items[] = new Quote\Item(
                $item,
                $this->_storeManager,
                $this->_categoryResource
            );
        }
        return $_items;
    }

    public function getSystem()
    {
        return new Quote\System(
            $this->_productMetadata,
            $this->_moduleResource
        );
    }

    public function getDuration() {

    }
}
