<?php
/*
 * (c) NETZKOLLEKTIV GmbH <kontakt@netzkollektiv.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Netzkollektiv\EasyCredit\Block\Ui;

use Magento\Catalog\Block\ShortcutInterface;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\View\Element\Template\Context;
use Magento\Quote\Model\QuoteIdMaskFactory;

class ExpressButton extends Widget implements ShortcutInterface
{
    /**
     * @var ScopeConfigInterface
     */
    public $scopeConfig;

    protected $_template = 'Netzkollektiv_EasyCredit::easycredit/ui/express-button.phtml';

    protected CheckoutSession $checkoutSession;

    private QuoteIdMaskFactory $quoteIdMaskFactory;

    public function __construct(
        Context $context,
        CheckoutSession $checkoutSession,
        QuoteIdMaskFactory $quoteIdMaskFactory,
        array $data = []
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->quoteIdMaskFactory = $quoteIdMaskFactory;
        $this->scopeConfig = $context->getScopeConfig();
        parent::__construct($context, $checkoutSession, $data);
    }

    public function getAlias()
    {
        return 'easycredit.express.button';
    }

    public function getQuoteId()
    {
        $quote = $this->checkoutSession->getQuote();
        $quoteIdMask = $this->quoteIdMaskFactory->create()->load($quote->getId(), 'quote_id');
        return $quoteIdMask->getMaskedId();
    }
}
