<?php
/*
 * (c) NETZKOLLEKTIV GmbH <kontakt@netzkollektiv.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Netzkollektiv\EasyCredit\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Checkout\Model\Cart;
use Magento\Framework\App\RequestInterface;
use Netzkollektiv\EasyCredit\Logger\Logger;

class ProductAddToCartBefore implements ObserverInterface
{
    public function __construct(
        private Cart $cart,
        private RequestInterface $request,
        private Logger $logger
    ) {
    }

    public function execute(Observer $observer) {
        if (!$this->request->getParam('easycredit-express-checkout')) {
            return;
        }

        $this->logger->debug('EasyCredit Express Checkout :: clearing cart');
        foreach ($this->cart->getQuote()->getAllItems() as $item) {
            /** @var \Magento\Quote\Model\Quote\Item $item */
            $item->delete();
        }
    }
}