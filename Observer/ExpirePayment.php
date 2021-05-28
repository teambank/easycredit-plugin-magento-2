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
        ManagerInterface $messageManager
    ) {
        $this->quoteRepository = $quoteRepository;
        $this->messageManager = $messageManager;
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

        $authorizedAmount = $quote->getPayment()
            ->getAdditionalInformation('authorized_amount');
        $interestAmount = $quote->getPayment()
            ->getAdditionalInformation('interest_amount');

        if ($authorizedAmount > 0
            && $interestAmount > 0
            && round($amount, 2) != round($authorizedAmount + $interestAmount, 2)
        ) {
            $quote->getPayment()->unsAdditionalInformation()->save();
        }
    }
}
