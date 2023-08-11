<?php
/*
 * (c) NETZKOLLEKTIV GmbH <kontakt@netzkollektiv.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Netzkollektiv\EasyCredit\Block\Ui;

use Netzkollektiv\EasyCredit\Helper\Data as EasyCreditHelper;
use Magento\Framework\View\Element\Template\Context;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Catalog\Block\ShortcutInterface;
use Magento\Quote\Model\QuoteIdMaskFactory;

class ExpressButton extends Widget implements ShortcutInterface
{
    protected $_template = 'Netzkollektiv_EasyCredit::easycredit/ui/express-button.phtml';

    public function __construct(
        Context $context,
        protected CheckoutSession $checkoutSession,
        private QuoteIdMaskFactory $quoteIdMaskFactory,
        array $data = []
    ) {
        $this->scopeConfig = $context->getScopeConfig();
        parent::__construct($context, $checkoutSession, $data);
    }

    public function getAlias() {
        return 'easycredit.express.button';
    }

    public function getQuoteId() {
        $quote = $this->checkoutSession->getQuote();
        $quoteIdMask = $this->quoteIdMaskFactory->create()->load($quote->getId(), 'quote_id');
        return $quoteIdMask->getMaskedId();
    }
}
