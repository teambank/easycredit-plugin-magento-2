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

    public function setQuote($quote) {
        $this->_quote = $quote;
        return $this;
    }

    public function getQuote() {
	    if (!$this->_quote) {
	        $this->_quote = $this->_checkoutSession->getQuote();
	    }
        return $this->_quote;
    }

    public function getId()
    {
        return $this->getQuote()->getId();
    }

    public function getShippingMethod()
    {
        if ($this->getQuote()->getShippingAddress()) {
            return $this->getQuote()->getShippingAddress()->getShippingMethod();
        }
    }

    public function getIsClickAndCollect()
    {
        if ($this->getQuote()->getShippingAddress() && $shippingMethod = $this->getQuote()->getShippingAddress()->getShippingMethod()) {
            return $shippingMethod === $this->scopeConfig->getValue('payment/easycredit/clickandcollect/shipping_method', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        }
        return false;
    }

    public function getGrandTotal()
    {
        return $this->getQuote()->getGrandTotal();
    }

    public function getBillingAddress()
    {
        return new Quote\Address($this->getQuote()->getBillingAddress());
    }

    public function getShippingAddress()
    {
        return new Quote\ShippingAddress($this->getQuote()->getShippingAddress());
    }

    public function getCustomer()
    {
        return new Quote\Customer(
            $this->_customerSession,
            $this->_salesOrderCollection,
            $this->getQuote()->getCustomer(),
            $this->getQuote()->getBillingAddress(),
            $this->getQuote()->getShippingAddress(),
            $this->_easyCreditHelper
        );
    }

    public function getItems()
    {
        return $this->_getItems(
            $this->getQuote()->getAllVisibleItems()
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
