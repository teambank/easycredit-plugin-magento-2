<?php
/*
 * (c) NETZKOLLEKTIV GmbH <kontakt@netzkollektiv.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Netzkollektiv\EasyCredit\Observer;

use Magento\Catalog\Block\ShortcutButtons;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Netzkollektiv\EasyCredit\Block\Ui\ExpressButton;

class AddEasyCreditShortcuts implements ObserverInterface
{
    /**
     * Add easyCredit shortcut buttons
     *
     * @param EventObserver $observer
     */
    public function execute(Observer $observer): void
    {
        /** @var ShortcutButtons $shortcutButtons */
        $shortcutButtons = $observer->getEvent()->getContainer();

        $shortcut = $shortcutButtons->getLayout()->createBlock(
            ExpressButton::class
        )->setIsInCatalogProduct($observer->getEvent()->getIsCatalogProduct())
            ->setShowOrPosition($observer->getEvent()->getOrPosition())
            ->setIsShoppingCart((bool) $observer->getEvent()->getIsShoppingCart());

        $shortcutButtons->addShortcut($shortcut);
    }
}
