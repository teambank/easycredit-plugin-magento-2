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
use Netzkollektiv\EasyCredit\BackendApi\QuoteBuilder;
use Netzkollektiv\EasyCredit\Helper\Data as EasyCreditHelper;
use Netzkollektiv\EasyCredit\Model\Payment;

class ExpirePayment implements ObserverInterface
{

    /**
     * @var EasyCreditHelper
     */
    private $easyCreditHelper;


    /**
     * @var QuoteBuilder
     */
    private $easyCreditQuoteBuilder;

    public function __construct(
        EasyCreditHelper $easyCreditHelper,
        QuoteBuilder $easyCreditQuoteBuilder
    ) {
        $this->easyCreditHelper = $easyCreditHelper;
        $this->easyCreditQuoteBuilder = $easyCreditQuoteBuilder;
    }

    public function execute(Observer $observer)
    {
        $event = $observer->getEvent();

        /**
         * @var Quote $quote
         */
        $quote = $event->getData('quote');

        $amount = $quote->getGrandTotal();

        if ($quote->getPayment()->getMethod() != Payment::CODE) {
            return;
        }

        if ($quote->getPayment()->getAdditionalInformation('interest_amount') === null) {
            return;
        }

        $checkout = $this->easyCreditHelper
            ->getCheckout($quote);

        $ecQuote = $this->easyCreditQuoteBuilder
            ->setQuote($quote)
            ->build();

        if (!$checkout->isValid($ecQuote)) {
            $checkout->clear();
        }
    }
}
