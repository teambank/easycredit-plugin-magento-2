<?php
/*
 * (c) NETZKOLLEKTIV GmbH <kontakt@netzkollektiv.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Netzkollektiv\EasyCredit\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\ResponseFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\UrlInterface;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Quote\Model\QuoteIdMaskFactory;

use Netzkollektiv\EasyCredit\Logger\Logger;

class ProductAddToCartComplete implements ObserverInterface
{
    public function __construct(
        private ResponseFactory $responseFactory,
        private RequestInterface $request,
        private UrlInterface $url,
        private Logger $logger,
        private CheckoutSession $checkoutSession,
        private QuoteIdMaskFactory $quoteIdMaskFactory
    ) {
    }

    public function execute(Observer $observer) {
        if (!$this->request->getParam('easycredit-express-checkout')) {
            return;
        }

        $this->logger->debug('EasyCredit Express Checkout :: sending redirect');

        $quote = $this->checkoutSession->getQuote();
        $quoteIdMask = $this->quoteIdMaskFactory->create()->load($quote->getId(), 'quote_id');

        $response = $this->responseFactory->create();
        $response->getHeaders()->addHeaders(['Content-Type'=>'application/json']);
        $response->setBody(\json_encode(['quoteId' => $quoteIdMask->getMaskedId()]));
        $response->sendResponse();
        exit;
    }
}
