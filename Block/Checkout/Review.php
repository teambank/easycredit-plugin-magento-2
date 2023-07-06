<?php
/*
 * (c) NETZKOLLEKTIV GmbH <kontakt@netzkollektiv.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

// @codingStandardsIgnoreFile

namespace Netzkollektiv\EasyCredit\Block\Checkout;

use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address;
use Magento\Customer\Model\Address\Config;
use Magento\Framework\View\Element\Template\Context;
use Magento\Customer\Block\Address\Renderer\RendererInterface;
use Magento\Framework\Convert\ConvertArray;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\View\Element\Template;
use Magento\Quote\Model\Quote\Address\Rate;
use Magento\Tax\Helper\Data as TaxHelper;
use Magento\Customer\Model\Address\Config as AddressConfig;

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
    private ?Quote $quote = null;

    private ?Address $address = null;

    private Config $addressConfig;

    /**
     * Currently selected shipping rate
     *
     * @var Rate
     */
    private $currentShippingRate;

    /**
     * EasyCredit controller path
     */
    private string $controllerPath = 'easycredit/checkout';

    private \Magento\Tax\Helper\Data $taxHelper;

    private PriceCurrencyInterface $priceCurrency;


    public function __construct(
        Context $context,
        \Magento\Tax\Helper\Data $taxHelper,
        Config $addressConfig,
        PriceCurrencyInterface $priceCurrency,
        array $data = []
    ) {
        $this->priceCurrency = $priceCurrency;
        $this->taxHelper = $taxHelper;
        $this->addressConfig = $addressConfig;
        parent::__construct($context, $data);
    }

    /**
     * Quote object setter
     *
     * @return $this
     */
    public function setQuote(Quote $quote)
    {
        $this->quote = $quote;
        return $this;
    }

    /**
     * Return quote billing address
     *
     * @return Address
     */
    public function getBillingAddress()
    {
        return $this->quote->getBillingAddress();
    }

    /**
     * Return quote shipping address
     *
     * @return false|Address
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
     * @param Address $address
     * @return string
     */
    public function renderAddress($address)
    {
        /** @var RendererInterface $renderer */
        $renderer = $this->addressConfig->getFormatByCode('html')->getRenderer();
        $addressData = ConvertArray::toFlatArray($address->getData());
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
        if ($name = $this->_scopeConfig->getValue(sprintf('carriers/%s/title', $carrierCode), ScopeInterface::SCOPE_STORE)) {
            return $name;
        }

        return $carrierCode;
    }

    /**
     * Get either shipping rate code or empty value on error
     *
     * @return string
     */
    public function renderShippingRateValue(DataObject $rate)
    {
        if ($rate->getErrorMessage()) {
            return '';
        }

        return $rate->getCode();
    }

    /**
     * Get shipping rate code title and its price or error message
     *
     * @param DataObject $rate
     * @param string $format
     * @param string $inclTaxFormat
     */
    public function renderShippingRateOption($rate, $format = '%s - %s%s', $inclTaxFormat = ' (%s %s)'): string
    {
        if (!$rate) {
            return '';
        }
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
     */
    public function canEditShippingMethod(): bool
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
     */
    public function setControllerPath(string $prefix): void
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
     * @return Template
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
            if ($groups && $this->address instanceof Address) {
                $this->setShippingRateGroups($groups);
                // determine current selected code & name
                foreach ($groups as $rates) {
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
                $this->getUrl(sprintf('%s/saveShippingMethod', $this->controllerPath), ['_secure' => true])
            )->setCanEditShippingAddress(false)
            ->setCanEditShippingMethod(false);
        }

        $this->setPlaceOrderUrl(
            $this->getUrl(sprintf('%s/placeOrder', $this->controllerPath), ['_secure' => true])
        );

        return parent::_beforeToHtml();
    }

    public function getPaymentPlan()
    {
        $summary = \json_decode((string) $this->quote->getPayment()->getAdditionalInformation('summary'), null, 512, JSON_THROW_ON_ERROR);
        if ($summary === false || $summary === null) {
            return null;
        }

        return json_encode($summary, JSON_THROW_ON_ERROR);
    }
}
