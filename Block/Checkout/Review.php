<?php
/*
 * (c) NETZKOLLEKTIV GmbH <kontakt@netzkollektiv.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

// @codingStandardsIgnoreFile

namespace Netzkollektiv\EasyCredit\Block\Checkout;

use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\View\Element\Template;
use Magento\Quote\Model\Quote\Address\Rate;

use Netzkollektiv\EasyCredit\Helper\Data as EasyCreditHelper;

/**
 * Class Review
 * 
 * @package Netzkollektiv\EasyCredit\Block\Checkout
 * @method setPlaceOrderUrl(string $url)
 * @method getPlaceOrderUrl()
 * @method setPaymentMethodTitle(string $title)
 * @method getPaymentMethodTitle()
 * @method setShippingRateRequired(bool $required)
 * @method getShippingRateRequired()
 * @method setShippingRateGroups(array $groups)
 * @method getShippingRateGroups()
 * @method setShippingMethodSubmitUrl(string $url)
 * @method getShippingMethodSubmitUrl()
 */
class Review extends Template
{
    /**
     * @var \Magento\Quote\Model\Quote
     */
    private $quote;

    /**
     * @var \Magento\Quote\Model\Quote\Address
     */
    private $address;

    /**
     * @var \Magento\Customer\Model\Address\Config
     */
    private $addressConfig;

    /**
     * Currently selected shipping rate
     *
     * @var Rate
     */
    private $currentShippingRate = null;

    /**
     * EasyCredit controller path
     *
     * @var string
     */
    private $controllerPath = 'easycredit/checkout';

    /**
     * @var \Magento\Tax\Helper\Data
     */
    private $taxHelper;

    /**
     * @var PriceCurrencyInterface
     */
    private $priceCurrency;

    /**
     * @var EasyCreditHelper
     */
    private $easycreditHelper;


    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Tax\Helper\Data $taxHelper,
        \Magento\Customer\Model\Address\Config $addressConfig,
        EasyCreditHelper $easycreditHelper,
        PriceCurrencyInterface $priceCurrency,
        array $data = []
    ) {
        $this->priceCurrency = $priceCurrency;
        $this->taxHelper = $taxHelper;
        $this->addressConfig = $addressConfig;
        $this->easycreditHelper = $easycreditHelper;
        parent::__construct($context, $data);
    }

    /**
     * Quote object setter
     *
     * @param \Magento\Quote\Model\Quote $quote
     * @return $this
     */
    public function setQuote(\Magento\Quote\Model\Quote $quote)
    {
        $this->quote = $quote;
        return $this;
    }

    /**
     * Return quote billing address
     *
     * @return \Magento\Quote\Model\Quote\Address
     */
    public function getBillingAddress()
    {
        return $this->quote->getBillingAddress();
    }

    /**
     * Return quote shipping address
     *
     * @return false|\Magento\Quote\Model\Quote\Address
     */
    public function getShippingAddress()
    {
        if ($this->quote->getIsVirtual()) {
            return false;
        }
        return $this->quote->getShippingAddress();
    }

    /**
     * Get HTML output for specified address
     *
     * @param \Magento\Quote\Model\Quote\Address $address
     * @return string
     */
    public function renderAddress($address)
    {
        /** @var \Magento\Customer\Block\Address\Renderer\RendererInterface $renderer */
        $renderer = $this->addressConfig->getFormatByCode('html')->getRenderer();
        $addressData = \Magento\Framework\Convert\ConvertArray::toFlatArray($address->getData());
        return $renderer->renderArray($addressData);
    }

    /**
     * Return carrier name from config, base on carrier code
     *
     * @param string $carrierCode
     * @return string
     */
    public function getCarrierName($carrierCode)
    {
        if ($name = $this->_scopeConfig->getValue("carriers/{$carrierCode}/title", \Magento\Store\Model\ScopeInterface::SCOPE_STORE)) {
            return $name;
        }
        return $carrierCode;
    }

    /**
     * Get either shipping rate code or empty value on error
     *
     * @param \Magento\Framework\DataObject $rate
     * @return string
     */
    public function renderShippingRateValue(\Magento\Framework\DataObject $rate)
    {
        if ($rate->getErrorMessage()) {
            return '';
        }
        return $rate->getCode();
    }

    /**
     * Get shipping rate code title and its price or error message
     *
     * @param \Magento\Framework\DataObject $rate
     * @param string $format
     * @param string $inclTaxFormat
     * @return string
     */
    public function renderShippingRateOption($rate, $format = '%s - %s%s', $inclTaxFormat = ' (%s %s)')
    {
        $renderedInclTax = '';
        if ($rate->getErrorMessage()) {
            $price = $rate->getErrorMessage();
        } else {
            $price = $this->_getShippingPrice(
                $rate->getPrice(),
                $this->taxHelper->displayShippingPriceIncludingTax()
            );

            $incl = $this->_getShippingPrice($rate->getPrice(), true);
            if ($incl != $price && $this->taxHelper->displayShippingBothPrices()) {
                $renderedInclTax = sprintf($inclTaxFormat, $this->escapeHtml(__('Incl. Tax')), $incl);
            }
        }
        return sprintf($format, $this->escapeHtml($rate->getMethodTitle()), $price, $renderedInclTax);
    }

    /**
     * Getter for current shipping rate
     *
     * @return Rate
     */
    public function getCurrentShippingRate()
    {
        return $this->currentShippingRate;
    }

    /**
     * Whether can edit shipping method
     *
     * @return bool
     */
    public function canEditShippingMethod()
    {
        return false;
    }

    /**
     * Get quote email
     *
     * @return string
     */
    public function getEmail()
    {
        $billingAddress = $this->getBillingAddress();
        return $billingAddress ? $billingAddress->getEmail() : '';
    }

    /**
     * Set controller path
     *
     * @param string $prefix
     * @return void
     */
    public function setControllerPath($prefix)
    {
        $this->controllerPath = $prefix;
    }

    /**
     * Return formatted shipping price
     *
     * @param float $price
     * @param bool $isInclTax
     * @return string
     */
    private function _getShippingPrice($price, $isInclTax)
    {
        return $this->_formatPrice($this->taxHelper->getShippingPrice($price, $isInclTax, $this->address));
    }

    /**
     * Format price base on store convert price method
     *
     * @param float $price
     * @return string
     */
    private function _formatPrice($price)
    {
        return $this->priceCurrency->convertAndFormat(
            $price,
            true,
            PriceCurrencyInterface::DEFAULT_PRECISION,
            $this->quote->getStore()
        );
    }

    /**
     * Retrieve payment method and assign additional template values
     *
     * @return \Magento\Framework\View\Element\Template
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    protected function _beforeToHtml()
    {
        $methodInstance = $this->quote->getPayment()->getMethodInstance();
        $this->setPaymentMethodTitle($methodInstance->getTitle());

        $this->setShippingRateRequired(true);
        if ($this->quote->getIsVirtual()) {
            $this->setShippingRateRequired(false);
        } else {
            // prepare shipping rates
            $this->address = $this->quote->getShippingAddress();
            $groups = $this->address->getGroupedAllShippingRates();
            if ($groups && $this->address) {
                $this->setShippingRateGroups($groups);
                // determine current selected code & name
                foreach ($groups as $code => $rates) {
                    foreach ($rates as $rate) {
                        if ($this->address->getShippingMethod() == $rate->getCode()) {
                            $this->currentShippingRate = $rate;
                            break 2;
                        }
                    }
                }
            }

            // misc shipping parameters
            $this->setShippingMethodSubmitUrl(
                $this->getUrl("{$this->controllerPath}/saveShippingMethod", ['_secure' => true])
            )->setCanEditShippingAddress(false)
            ->setCanEditShippingMethod(false);
        }

        $this->setPlaceOrderUrl(
            $this->getUrl("{$this->controllerPath}/placeOrder", ['_secure' => true])
        );

        return parent::_beforeToHtml();
    }

    public function getPaymentPlan()
    {
        $summary = \json_decode((string) $this->quote->getPayment()->getAdditionalInformation('summary'));
        if ($summary === false || $summary === null) {
            return null;
        }
        return json_encode($summary);
    }
}
