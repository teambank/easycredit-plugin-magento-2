<?php
/*
 * (c) NETZKOLLEKTIV GmbH <kontakt@netzkollektiv.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Netzkollektiv\EasyCredit\Controller\Checkout;

class Review extends AbstractController
{

    /**
     * @var \Magento\Framework\App\ViewInterface
     */
    protected $view;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Customer\Model\Url $customerUrl,
        \Netzkollektiv\EasyCredit\Helper\Data $easyCreditHelper,
        \Psr\Log\LoggerInterface $logger
    ) {
        parent::__construct($context, $checkoutSession, $customerUrl);

        $this->easyCreditCheckout = $easyCreditHelper->getCheckout();
        $this->logger = $logger;
    }

    /**
     * Dispatch request
     *
     * @return ResultInterface|ResponseInterface
     * @throws NotFoundException
     */
    public function execute()
    {
        try {
            $this->_validateQuote();
            if (!$this->easyCreditCheckout->isInitialized()) {
                $this->messageManager->addErrorMessage(
                    __('Unable to initialize easyCredit Checkout review. Not initialized.')
                );
                $this->_redirect('checkout/cart');
            }

            $this->_view->loadLayout();
            $reviewBlock = $this->_view->getLayout()->getBlock('easycredit.checkout.review');
            $reviewBlock->setQuote($this->checkoutSession->getQuote());
            $reviewBlock->getChildBlock('details')->setQuote($this->checkoutSession->getQuote());
            if ($reviewBlock->getChildBlock('shipping_method')) {
                $reviewBlock->getChildBlock('shipping_method')->setQuote($this->checkoutSession->getQuote());
            }
            $this->_view->renderLayout();

            return;
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('Unable to initialize easyCredit Checkout review.'));
            $this->logger->critical($e);
        }
        $this->messageManager->addErrorMessage(__('Unable to initialize easyCredit Checkout review.'));
        $this->_redirect('checkout/cart');
    }
}
