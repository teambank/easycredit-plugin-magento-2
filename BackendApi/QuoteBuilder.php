<?php
/*
 * (c) NETZKOLLEKTIV GmbH <kontakt@netzkollektiv.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Netzkollektiv\EasyCredit\BackendApi;

use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\UrlInterface;
use Magento\Quote\Model\Quote;
use Magento\Sales\Model\ResourceModel\Order\Collection as OrderCollection;
use Magento\Store\Model\ScopeInterface;
use Netzkollektiv\EasyCredit\BackendApi\Quote\AddressBuilder;
use Netzkollektiv\EasyCredit\BackendApi\Quote\CustomerBuilder;
use Netzkollektiv\EasyCredit\Helper\Payment as PaymentHelper;
use Netzkollektiv\EasyCredit\BackendApi\Quote\ItemBuilder;
use Netzkollektiv\EasyCredit\BackendApi\Quote\SystemBuilder;
use Teambank\RatenkaufByEasyCreditApiV3 as Api;
use Teambank\RatenkaufByEasyCreditApiV3\Model\RedirectLinks;
use Teambank\RatenkaufByEasyCreditApiV3\Model\ShoppingCartInformationItem;

class QuoteBuilder
{
    private CheckoutSession $checkoutSession;

    private CustomerSession $customerSession;

    private OrderCollection $salesOrderCollection;

    private ScopeConfigInterface $scopeConfig;

    private UrlInterface $url;

    private StorageFactory $storageFactory;

    private PaymentHelper $paymentHelper;

    private AddressBuilder $addressBuilder;

    private ItemBuilder $itemBuilder;

    private SystemBuilder $systemBuilder;

    private CustomerBuilder $customerBuilder;

    private ?Quote $quote = null;

    public function __construct(
        CheckoutSession $checkoutSession,
        CustomerSession $customerSession,
        OrderCollection $salesOrderCollection,
        ScopeConfigInterface $scopeConfig,
        UrlInterface $url,
        StorageFactory $storageFactory,
        PaymentHelper $paymentHelper,
        AddressBuilder $addressBuilder,
        ItemBuilder $itemBuilder,
        SystemBuilder $systemBuilder,
        CustomerBuilder $customerBuilder
    ) {
        $this->customerSession = $customerSession;
        $this->checkoutSession = $checkoutSession;
        $this->salesOrderCollection = $salesOrderCollection;
        $this->scopeConfig = $scopeConfig;
        $this->url = $url;
        $this->storageFactory = $storageFactory;
        $this->paymentHelper = $paymentHelper;

        $this->addressBuilder = $addressBuilder;
        $this->itemBuilder = $itemBuilder;
        $this->systemBuilder = $systemBuilder;
        $this->customerBuilder = $customerBuilder;
    }

    public function setQuote(?Quote $quote)
    {
        $this->quote = $quote;
        return $this;
    }

    public function getQuote(): ?Quote
    {
        if (!$this->quote instanceof Quote) {
            $this->quote = $this->checkoutSession->getQuote();
        }

        return $this->quote;
    }

    private function getId()
    {
        return $this->getQuote()->getId();
    }

    public function getPaymentType()
    {
        return $this->paymentHelper->getTypeByMethod(
            $this->getQuote()->getPayment()->getMethod()
        ) . '_PAYMENT';
    }

    private function getShippingMethod()
    {
        $shippingMethod = $this->getQuote()->getShippingAddress()->getShippingMethod();

        if ($this->getIsClickAndCollect()) {
            $shippingMethod = '[Selbstabholung] ' . $shippingMethod;
        }

        if ($shippingMethod !== '') {
            return $shippingMethod;
        }

        return '';
    }

    private function getIsClickAndCollect(): bool
    {
        if (!$this->getQuote()->getShippingAddress()) {
            return false;
        }

        $shippingMethod = $this->getQuote()->getShippingAddress()->getShippingMethod();
        if ($shippingMethod === '') {
            return false;
        }

        if ($shippingMethod === null) {
            return false;
        }

        return $shippingMethod === $this->scopeConfig->getValue('payment/easycredit/clickandcollect/shipping_method', ScopeInterface::SCOPE_STORE);
    }

    private function getGrandTotal()
    {
        return $this->getQuote()->getGrandTotal();
    }

    /**
     * @return ShoppingCartInformationItem[]
     */
    private function getItems(): array
    {
        $items = [];
        foreach ($this->getQuote()->getAllVisibleItems() as $item) {
            $items[] = $this->itemBuilder->build(
                $item
            );
        }

        return $items;
    }

    private function getDuration(): ?string
    {
        return $this->storageFactory->create(
            [
                'payment' => $this->getQuote()->getPayment(),
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

    private function isExpress()
    {
        return $this->storageFactory->create(
            [
                'payment' => $this->getQuote()->getPayment(),
            ]
        )->get('express');
    }

    private function getRedirectLinks(): RedirectLinks
    {
        $storage = $this->storageFactory->create(
            [
                'payment' => $this->getQuote()->getPayment(),
            ]
        );

        return new Api\Model\RedirectLinks(
            [
                'urlSuccess' => $this->url->getUrl('easycredit/checkout/return'),
                'urlCancellation' => $this->url->getUrl('easycredit/checkout/cancel'),
                'urlDenial' => $this->url->getUrl('easycredit/checkout/reject'),
            ]
        );
    }

    public function build(): Api\Model\Transaction
    {
        return new Api\Model\Transaction(
            [
                'paymentType' => $this->getPaymentType(),
                'financingTerm' => $this->getDuration(),
                'orderDetails' => new Api\Model\OrderDetails(
                    [
                        'orderValue' => $this->getGrandTotal(),
                        'orderId' => $this->getId(),
                        'numberOfProductsInShoppingCart' => is_countable($this->getQuote()->getAllVisibleItems()) ? count($this->getQuote()->getAllVisibleItems()) : 1,
                        'invoiceAddress' => $this->isExpress() ? null : $this->addressBuilder
                            ->setAddress(new Api\Model\InvoiceAddress())
                            ->build($this->getQuote()->getBillingAddress()),
                        'shippingAddress' => $this->isExpress() ? null : $this->addressBuilder
                            ->setAddress(new Api\Model\ShippingAddress())
                            ->build($this->getQuote()->getShippingAddress()),
                        'shoppingCartInformation' => $this->getItems(),
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
                        'logisticsServiceProvider' => $this->getShippingMethod(),
                    ]
                ),
                'redirectLinks' => $this->getRedirectLinks(),
            ]
        );
    }
}
