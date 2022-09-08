<?php
/*
 * (c) NETZKOLLEKTIV GmbH <kontakt@netzkollektiv.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Netzkollektiv\EasyCredit\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\Quote;

use Netzkollektiv\EasyCredit\Model\Payment;

class ExpirePayment implements ObserverInterface
{

    /**
     * @var CartRepositoryInterface
     */
    private $quoteRepository;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    public function __construct(
        CartRepositoryInterface $quoteRepository,
        ManagerInterface $messageManager,
        \Netzkollektiv\EasyCredit\Helper\Data $easyCreditHelper,
        \Netzkollektiv\EasyCredit\BackendApi\Quote $easyCreditQuote
    ) {
        $this->quoteRepository = $quoteRepository;
        $this->messageManager = $messageManager;
        $this->easyCreditCheckout = $easyCreditHelper->getCheckout();
        $this->easyCreditQuote = $easyCreditQuote;
    }

    public function execute(Observer $observer)
    {
        $event = $observer->getEvent();

        /**
         * @var Quote $quote
         */
        $quote = $event->getQuote();

        $amount = $quote->getGrandTotal();

        if ($quote->getPayment()->getMethod() != Payment::CODE) {
            return;
        }

        if ($quote->getPayment()->getAdditionalInformation('interest_amount') === null) {
            return;
        }

        $checkout = $this->easyCreditCheckout;
        $ecQuote = $this->easyCreditQuote;
        if (!$checkout->isAmountValid($ecQuote)
            || !$checkout->verifyAddressNotChanged($ecQuote)
            || !$checkout->sameAddresses($ecQuote)
        ) {
            $quote->getPayment()->unsAdditionalInformation()->save();
        }
    }
}
