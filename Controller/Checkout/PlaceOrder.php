<?php
/*
 * (c) NETZKOLLEKTIV GmbH <kontakt@netzkollektiv.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Netzkollektiv\EasyCredit\Controller\Checkout;

use Magento\Customer\Model\Session;
use Magento\Quote\Api\CartManagementInterface;
use Netzkollektiv\EasyCredit\Logger\Logger;
use Magento\Framework\App\Action\Context;
use Magento\Customer\Model\Url;
use Magento\Framework\Exception\LocalizedException;
use Netzkollektiv\EasyCredit\BackendApi\QuoteBuilder;
use Netzkollektiv\EasyCredit\Helper\Data as EasyCreditHelper;
use Magento\Checkout\Model\Type\Onepage;

class PlaceOrder extends AbstractController
{

    private Session $customerSession;

    /**
     * Checkout data
     */
    private \Magento\Checkout\Helper\Data $checkoutData;

    private CartManagementInterface $cartManagement;

    private QuoteBuilder $easyCreditQuoteBuilder;

    private EasyCreditHelper $easyCreditHelper;

    private Logger $logger;

    public function __construct(
        Context $context,
        \Magento\Checkout\Model\Session $checkoutSession,
        Url $customerUrl,
        Session $customerSession,
        EasyCreditHelper $easyCreditHelper,
        QuoteBuilder $easyCreditQuoteBuilder,
        CartManagementInterface $cartManagement,
        \Magento\Checkout\Helper\Data $checkoutData,
        Logger $logger
    ) {
        $this->customerSession = $customerSession;
        $this->easyCreditHelper = $easyCreditHelper;
        $this->easyCreditQuoteBuilder = $easyCreditQuoteBuilder;
        $this->cartManagement = $cartManagement;
        $this->checkoutData = $checkoutData;
        $this->logger = $logger;

        parent::__construct($context, $checkoutSession, $customerUrl);
    }

    /**
     * Dispatch request
     */
    public function execute(): void
    {
        $ecCheckout = $this->easyCreditHelper->getCheckout();
        if (!$ecCheckout->isInitialized()) {
            $this->messageManager->addErrorMessage(
                __('Unable to finish easyCredit Checkout. Please restart payment process.')
            );
            $this->_redirect('checkout/cart');
            return;
        }

        $ecQuote = $this->easyCreditQuoteBuilder->build();

        if (!$ecCheckout->isValid($ecQuote)) {
            $this->messageManager->addErrorMessage(
                __("Unable to finish easyCredit Checkout. Validation failed.")
            );

            $ecCheckout->clear();
            $this->_redirect('checkout/cart');
            return;
        }

        $quote = $this->checkoutSession->getQuote();

        if (!$this->customerSession->isLoggedIn()) {
            if ($this->checkoutData->isAllowedGuestCheckout($quote)) {
                $quote->setCheckoutMethod(Onepage::METHOD_GUEST);
            } else {
                $quote->setCheckoutMethod(Onepage::METHOD_REGISTER);
            }
        } else {
            $quote->setCheckoutMethod(Onepage::METHOD_CUSTOMER);
        }

        try {
            $orderId = $this->cartManagement->placeOrder($quote->getId());
        } catch (LocalizedException $localizedException) {
            $this->logger->error($localizedException->getMessage());
            $this->messageManager->addErrorMessage(__($localizedException->getMessage()));
            $this->_redirect('easycredit/checkout/cancel');
        }

        $this->_redirect('checkout/onepage/success');
    }
}
