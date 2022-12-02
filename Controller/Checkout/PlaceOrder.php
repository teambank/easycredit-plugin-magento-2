<?php
/*
 * (c) NETZKOLLEKTIV GmbH <kontakt@netzkollektiv.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Netzkollektiv\EasyCredit\Controller\Checkout;

use Netzkollektiv\EasyCredit\BackendApi\QuoteBuilder;
use Netzkollektiv\EasyCredit\Helper\Data as EasyCreditHelper;

class PlaceOrder extends AbstractController
{

    /**
     * @var \Magento\Customer\Model\Session
     */
    private $customerSession;

    /**
     * Checkout data
     *
     * @var \Magento\Checkout\Helper\Data
     */
    private $checkoutData;

    /**
     * @var \Magento\Quote\Api\CartManagementInterface
     */
    private $cartManagement;

    /**
     * @var QuoteBuilder
     */
    private $easyCreditQuoteBuilder;

    /**
     * @var EasyCreditHelper
     */
    private $easyCreditHelper;

    /**
     * @var \Netzkollektiv\EasyCredit\Logger\Logger
     */
    private $logger;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Customer\Model\Url $customerUrl,
        \Magento\Customer\Model\Session $customerSession,
        EasyCreditHelper $easyCreditHelper,
        QuoteBuilder $easyCreditQuoteBuilder,
        \Magento\Quote\Api\CartManagementInterface $cartManagement,
        \Magento\Checkout\Helper\Data $checkoutData,
        \Netzkollektiv\EasyCredit\Logger\Logger $logger
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
     *
     * @return void
     */
    public function execute()
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
            if (!$this->checkoutData->isAllowedGuestCheckout($quote)) {
                $this->messageManager->addErrorMessage(__('Guest checkout is not allowed.'));
                $this->_redirect('checkout/cart');
                return;
            }

            $quote->setCustomerId(0)
                ->setCustomerEmail($quote->getBillingAddress()->getEmail())
                ->setCustomerIsGuest(true)
                ->setCustomerGroupId(\Magento\Customer\Model\Group::NOT_LOGGED_IN_ID);
        }

        try {
            $orderId = $this->cartManagement->placeOrder($quote->getId());
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->logger->error($e->getMessage());
            $this->messageManager->addErrorMessage(__($e->getMessage()));
            $this->_redirect('easycredit/checkout/cancel');
        }

        $this->_redirect('checkout/onepage/success');
    }
}
