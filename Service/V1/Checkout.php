<?php
/*
 * (c) NETZKOLLEKTIV GmbH <kontakt@netzkollektiv.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Netzkollektiv\EasyCredit\Service\V1;

use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Quote\Api\CartRepositoryInterface;

use Netzkollektiv\EasyCredit\Api\CheckoutInterface;
use Netzkollektiv\EasyCredit\Api\Data\CheckoutDataInterface;
use Netzkollektiv\EasyCredit\BackendApi\Quote;
use Netzkollektiv\EasyCredit\Helper\Data;

class Checkout implements CheckoutInterface
{
    protected $checkoutSession;

    public function __construct(
        CheckoutSession $checkoutSession,
        CartRepositoryInterface $quoteRepository,
        Data $easyCreditHelper,
        CheckoutDataInterface $checkoutData,
        Quote $easyCreditQuote
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->quoteRepository = $quoteRepository;
        $this->easyCreditQuote = $easyCreditQuote;
        $this->easyCreditHelper = $easyCreditHelper;
        $this->easyCreditCheckout = $easyCreditHelper->getCheckout();
        $this->checkoutData = $checkoutData;
    }

    /**
     * @api
     * @param string $prefix
     * @return bool
     */
    public function isPrefixValid($prefix)
    {
        return $this->easyCreditCheckout->isPrefixValid($prefix);
    }

    /**
     * @api
     * @param string $cartId
     * @return \Netzkollektiv\EasyCredit\Api\Data\CheckoutDataInterface
     */
    public function getCheckoutData($cartId, $prefix = null)
    {
        try {
            $this->easyCreditCheckout->isAvailable(
                $this->easyCreditQuote
            );
            $this->checkoutData->setAgreement($this->easyCreditCheckout->getAgreement());
        } catch (\Exception $e) {
            $this->checkoutData->setErrorMessage($e->getMessage());
        }

        $this->checkoutData->setIsPrefixValid(
            $this->isPrefixValid(
                $this->easyCreditQuote->getCustomer()->getPrefix()
            )
        );

        return $this->checkoutData;
    }
}
