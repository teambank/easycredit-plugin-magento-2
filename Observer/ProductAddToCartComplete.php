<?php
/*
 * (c) NETZKOLLEKTIV GmbH <kontakt@netzkollektiv.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Netzkollektiv\EasyCredit\Observer;

use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseFactory;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Quote\Model\QuoteIdMaskFactory;
use Netzkollektiv\EasyCredit\Logger\Logger;

class ProductAddToCartComplete implements ObserverInterface
{
    private ResponseFactory $responseFactory;

    private RequestInterface $request;

    private Logger $logger;

    private CheckoutSession $checkoutSession;

    private QuoteIdMaskFactory $quoteIdMaskFactory;

    public function __construct(ResponseFactory $responseFactory, RequestInterface $request, Logger $logger, CheckoutSession $checkoutSession, QuoteIdMaskFactory $quoteIdMaskFactory)
    {
        $this->responseFactory = $responseFactory;
        $this->request = $request;
        $this->logger = $logger;
        $this->checkoutSession = $checkoutSession;
        $this->quoteIdMaskFactory = $quoteIdMaskFactory;
    }

    public function execute(Observer $observer): void
    {
        $params = $this->request->getParam('easycredit');
        if (!$params || !isset($params['express'])) {
            return;
        }

        $this->logger->debug('EasyCredit Express Checkout :: sending redirect');

        $quote = $this->checkoutSession->getQuote();
        $quoteIdMask = $this->quoteIdMaskFactory->create()->load($quote->getId(), 'quote_id');

        $response = $this->responseFactory->create();
        $response->getHeaders()->addHeaders([
            'Content-Type' => 'application/json',
        ]);
        $response->setBody(\json_encode([
            'quoteId' => $quoteIdMask->getMaskedId(),
        ], JSON_THROW_ON_ERROR));
        $response->sendResponse();
        exit;
    }
}
