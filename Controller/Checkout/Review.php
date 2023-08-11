<?php
/*
 * (c) NETZKOLLEKTIV GmbH <kontakt@netzkollektiv.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Netzkollektiv\EasyCredit\Controller\Checkout;

use Magento\Checkout\Model\Session;
use Magento\Customer\Model\Url;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ViewInterface;
use Magento\Framework\View\Element\BlockInterface;
use Netzkollektiv\EasyCredit\Helper\Data;
use Psr\Log\LoggerInterface;
use Teambank\RatenkaufByEasyCreditApiV3\Integration\Checkout;

class Review extends AbstractController
{
    /**
     * @var ViewInterface
     */
    protected $view;

    protected ?Checkout $easyCreditCheckout;

    protected LoggerInterface $logger;

    public function __construct(
        Context $context,
        Session $checkoutSession,
        Url $customerUrl,
        Data $easyCreditHelper,
        LoggerInterface $logger
    ) {
        parent::__construct($context, $checkoutSession, $customerUrl);

        $this->easyCreditCheckout = $easyCreditHelper->getCheckout();
        $this->logger = $logger;
    }

    /**
     * Dispatch request
     */
    public function execute(): void
    {
        try {
            $this->_validateQuote();
            if (! $this->easyCreditCheckout->isInitialized()) {
                $this->messageManager->addErrorMessage(
                    __('Unable to initialize easyCredit Checkout review. Not initialized.')
                );
                $this->_redirect('checkout/cart');
            }

            $this->_view->loadLayout();
            $reviewBlock = $this->_view->getLayout()->getBlock('easycredit.checkout.review');
            if ($reviewBlock instanceof BlockInterface) {
                $reviewBlock->setQuote($this->checkoutSession->getQuote());
                $reviewBlock->getChildBlock('details')->setQuote($this->checkoutSession->getQuote());
                if ($reviewBlock->getChildBlock('shipping_method')) {
                    $reviewBlock->getChildBlock('shipping_method')->setQuote($this->checkoutSession->getQuote());
                }
            }

            $this->_view->renderLayout();

            return;
        } catch (\Exception $exception) {
            $this->messageManager->addErrorMessage(__('Unable to initialize easyCredit Checkout review.'));
            $this->logger->critical($exception);
        }

        $this->messageManager->addErrorMessage(__('Unable to initialize easyCredit Checkout review.'));
        $this->_redirect('checkout/cart');
    }
}
