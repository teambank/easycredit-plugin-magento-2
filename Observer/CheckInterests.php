<?php
/*
 * (c) NETZKOLLEKTIV GmbH <kontakt@netzkollektiv.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Netzkollektiv\EasyCredit\Observer;

use Magento\Checkout\Model\Session;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Quote\Api\CartRepositoryInterface;

class CheckInterests implements ObserverInterface
{

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * @var CartRepositoryInterface
     */
    private $quoteRepository;

    public function __construct(
        Session $checkoutSession,
        CartRepositoryInterface $quoteRepository
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->quoteRepository = $quoteRepository;
    }

    public function execute(Observer $observer)
    {
        $quote = $this->checkoutSession->getQuote();
        $payment = $quote->getPayment();
        $amount = $payment->getAdditionalInformation('interest_amount');

        if ($amount) {
            $payment->unsAdditionalInformation();
            $quote->setTotalsCollectedFlag(false);
            $quote->collectTotals();
            $quote->setEasycreditAmount(null);
            $quote->setBaseEasycreditAmount(null);

            $this->quoteRepository->save($quote);
        }
    }
}
