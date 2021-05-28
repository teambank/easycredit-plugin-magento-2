<?php
/*
 * (c) NETZKOLLEKTIV GmbH <kontakt@netzkollektiv.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Netzkollektiv\EasyCredit\Controller\Checkout;

class Start extends AbstractController
{
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Customer\Model\Url $customerUrl,
        \Netzkollektiv\EasyCredit\Helper\Data $easyCreditHelper,
        \Netzkollektiv\EasyCredit\BackendApi\Quote $easyCreditQuote
    ) {
        parent::__construct(
            $context,
            $checkoutSession,
            $customerUrl
        );
        $this->easyCreditCheckout = $easyCreditHelper->getCheckout();
        $this->easyCreditQuote = $easyCreditQuote;
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

            $this->easyCreditCheckout->start(
                $this->easyCreditQuote,
                $this->_url->getUrl('easycredit/checkout/cancel'),
                $this->_url->getUrl('easycredit/checkout/return'),
                $this->_url->getUrl('easycredit/checkout/reject')
            );

            $quote = $this->checkoutSession->getQuote();
            $quote->getPayment()->save();
            $quote->collectTotals()->save();

            if ($url = $this->easyCreditCheckout->getRedirectUrl()) {
                $this->getResponse()->setRedirect($url);
                return null;
            }
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }

        $this->_redirect('checkout/cart');
        return null;
    }
}
