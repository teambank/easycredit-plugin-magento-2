<?php
/*
 * (c) NETZKOLLEKTIV GmbH <kontakt@netzkollektiv.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Netzkollektiv\EasyCredit\BackendApi;

use Magento\Catalog\Model\ResourceModel\Category;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\Module\ResourceInterface;
use Magento\Framework\UrlInterface;
use Magento\Sales\Model\ResourceModel\Order\Collection as OrderCollection;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Quote\Model\Quote;

use Netzkollektiv\EasyCredit\BackendApi\Quote\AddressBuilder;
use Netzkollektiv\EasyCredit\BackendApi\Quote\ItemBuilder;
use Netzkollektiv\EasyCredit\BackendApi\Quote\SystemBuilder;
use Netzkollektiv\EasyCredit\BackendApi\Quote\CustomerBuilder;

use Netzkollektiv\EasyCredit\Helper\Data as EasyCreditHelper;
use Teambank\RatenkaufByEasyCreditApiV3 as Api;

class QuoteBuilder
{
    /**
     * @var CheckoutSession
     */
    private $checkoutSession;

    /**
     * @var CustomerSession
     */
    private $customerSession;

    /**
     * @var OrderCollection
     */
    private $salesOrderCollection;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var Category
     */
    private $categoryResource;

    /**
     * @var ProductMetadataInterface
     */
    private $productMetadata;

    /**
     * @var ResourceInterface
     */
    private $moduleResource;

    /**
     * @var EasyCreditHelper
     */
    private $easyCreditHelper;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var UrlInterface
     */
    private $url;

    /**
     * @var StorageFactory
     */
    private $storageFactory;

    /**
     * @var AddressBuilder
     */
    private $addressBuilder;

    /**
     * @var ItemBuilder
     */
    private $itemBuilder;

    /**
     * @var SystemBuilder
     */
    private $systemBuilder;

    /**
     * @var CustomerBuilder
     */
    private $customerBuilder;

    /**
     * @var Quote
     */
    private $quote;

    public function __construct(
        CheckoutSession $checkoutSession,
        CustomerSession $customerSession,
        OrderCollection $salesOrderCollection,
        StoreManagerInterface $storeManager,
        Category $categoryResource,
        ProductMetadataInterface $productMetadata,
        ResourceInterface $moduleResource,
        EasyCreditHelper $easyCreditHelper,
        ScopeConfigInterface $scopeConfig,
        UrlInterface $url,
        StorageFactory $storageFactory,
        AddressBuilder $addressBuilder,
        ItemBuilder $itemBuilder,
        SystemBuilder $systemBuilder,
        CustomerBuilder $customerBuilder
    ) {
        $this->customerSession = $customerSession;
        $this->checkoutSession = $checkoutSession;
        $this->salesOrderCollection = $salesOrderCollection;
        $this->storeManager = $storeManager;
        $this->categoryResource = $categoryResource;
        $this->productMetadata = $productMetadata;
        $this->moduleResource = $moduleResource;
        $this->easyCreditHelper = $easyCreditHelper;
        $this->scopeConfig = $scopeConfig;
        $this->url = $url;
        $this->storageFactory = $storageFactory;

        $this->addressBuilder = $addressBuilder;
        $this->itemBuilder = $itemBuilder;
        $this->systemBuilder = $systemBuilder;
        $this->customerBuilder = $customerBuilder;
    }

    public function setQuote($quote)
    {
        $this->quote = $quote;
        return $this;
    }

    public function getQuote()
    {
        if (!$this->quote) {
            $this->quote = $this->checkoutSession->getQuote();
        }
        return $this->quote;
    }

    private function getId()
    {
        return $this->getQuote()->getId();
    }

    private function getShippingMethod()
    {
        $shippingMethod = '';
        if ($this->getQuote()->getShippingAddress()) {
            $shippingMethod = $this->getQuote()->getShippingAddress()->getShippingMethod();
        }
        if ($this->getIsClickAndCollect()) {
            $shippingMethod = '[Selbstabholung] ' . $shippingMethod;
        }
        return $shippingMethod;
    }

    private function getIsClickAndCollect()
    {
        if ($this->getQuote()->getShippingAddress() && $shippingMethod = $this->getQuote()->getShippingAddress()->getShippingMethod()) {
            return $shippingMethod === $this->scopeConfig->getValue('payment/easycredit/clickandcollect/shipping_method', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        }
        return false;
    }

    private function getGrandTotal()
    {
        return $this->getQuote()->getGrandTotal();
    }

    private function getItems()
    {
        $items = [];
        foreach ($this->getQuote()->getAllVisibleItems() as $item) {
            $items[] = $this->itemBuilder->build(
                $item
            );
        }
        return $items;
    }

    private function _getItems($items)
    {

    }

    private function getDuration(): ?string
    {
        return $this->storageFactory->create(
            [
            'payment' => $this->getQuote()->getPayment()
            ]
        )->get('duration');
    }

    private function getCustomerOrderCount()
    {
        if (!$this->customerSession->isLoggedIn()) {
            return 0;
        }
        return $this->salesOrderCollection
            ->addFieldToSelect('*')
            ->addFieldToFilter('customer_id', $this->getQuote()->getCustomer()->getId())
            ->count();
    }

    private function getCustomerCreatedAt()
    {
        if (!$this->customerSession->isLoggedIn()) {
            return null;
        }
        return \DateTime::createFromFormat('Y-m-d H:i:s', $this->getQuote()->getCustomer()->getCreatedAt());
    }

    private function getRedirectLinks()
    {
        $storage = $this->storageFactory->create(
            [
            'payment' => $this->getQuote()->getPayment()
            ]
        );

        if (!$storage->get('sec_token')) {
            $storage->set('sec_token', bin2hex(random_bytes(20)));
        }

        return new Api\Model\RedirectLinks(
            [
            'urlSuccess' => $this->url->getUrl('easycredit/checkout/return'),
            'urlCancellation' => $this->url->getUrl('easycredit/checkout/cancel'),
            'urlDenial' => $this->url->getUrl('easycredit/checkout/reject'),
            'urlAuthorizationCallback' =>  $this->url->getUrl(
                'easycredit/checkout/authorize', [
                'secToken' => $storage->get('sec_token')
                ]
            )
            ]
        );
    }

    public function build(): Api\Model\Transaction
    {
        return new Api\Model\Transaction(
            [
            'financingTerm' => $this->getDuration(),
            'orderDetails' => new Api\Model\OrderDetails(
                [
                'orderValue' => $this->getGrandTotal(),
                'orderId' => $this->getId(),
                'numberOfProductsInShoppingCart' => count($this->getQuote()->getAllVisibleItems()),
                'invoiceAddress' => $this->addressBuilder
                    ->setAddress(new Api\Model\InvoiceAddress())
                    ->build($this->getQuote()->getBillingAddress()),
                'shippingAddress' => $this->addressBuilder
                    ->setAddress(new Api\Model\ShippingAddress())
                    ->build($this->getQuote()->getShippingAddress()),
                'shoppingCartInformation' => $this->getItems()
                ]
            ),
            'shopsystem' => $this->systemBuilder->build(),
            'customer' => $this->customerBuilder->build(
                $this->getQuote()
            ),
            'customerRelationship' => new Api\Model\CustomerRelationship(
                [
                'customerSince' => $this->getCustomerCreatedAt(),
                'orderDoneWithLogin' => $this->customerSession->isLoggedIn(),
                'numberOfOrders' => $this->getCustomerOrderCount(),
                'logisticsServiceProvider' => $this->getShippingMethod()
                ]
            ),
            'redirectLinks' => $this->getRedirectLinks()
            ]
        );
    }
}
