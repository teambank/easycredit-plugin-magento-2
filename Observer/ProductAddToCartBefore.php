<?php
/*
 * (c) NETZKOLLEKTIV GmbH <kontakt@netzkollektiv.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Netzkollektiv\EasyCredit\Observer;

use Magento\Checkout\Model\Cart;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Quote\Api\CartItemRepositoryInterface;
use Magento\Quote\Model\Quote\Item;
use Netzkollektiv\EasyCredit\Logger\Logger;

class ProductAddToCartBefore implements ObserverInterface
{
    private Cart $cart;

    private CartItemRepositoryInterface $cartItemRepository;

    private RequestInterface $request;

    private Logger $logger;

    public function __construct(Cart $cart, CartItemRepositoryInterface $cartItemRepository, RequestInterface $request, Logger $logger)
    {
        $this->cart = $cart;
        $this->cartItemRepository = $cartItemRepository;
        $this->request = $request;
        $this->logger = $logger;
    }

    public function execute(Observer $observer): void
    {
        if (! $this->request->getParam('easycredit-express-checkout')) {
            return;
        }

        $this->logger->debug('EasyCredit Express Checkout :: clearing cart');
        $quote = $this->cart->getQuote();
        foreach ($quote->getAllItems() as $item) {
            /** @var Item $item */
            $this->cartItemRepository->deleteById($quote->getId(), $item->getItemId());
        }
    }
}
