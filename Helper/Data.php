<?php
/*
 * (c) NETZKOLLEKTIV GmbH <kontakt@netzkollektiv.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Netzkollektiv\EasyCredit\Helper;

use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Quote\Api\CartRepositoryInterface;

class Data extends AbstractHelper
{
    protected $checkoutSession;

    protected $config;
    protected $logger;

    protected $installmentValues;

    public function __construct(
        Context $context,
        CheckoutSession $checkoutSession,
        CartRepositoryInterface $quoteRepository,
        \Netzkollektiv\EasyCredit\BackendApi\Config $config,
        \Netzkollektiv\EasyCredit\BackendApi\Logger $logger
    ) {
        parent::__construct(
            $context
        );

        $this->installmentValues = [];
        $this->checkoutSession = $checkoutSession;
        $this->quoteRepository = $quoteRepository;
        $this->config = $config;
        $this->logger = $logger;
    }

    public function getCheckout()
    {
        if (!isset($this->checkout)) {
            $client = new \Netzkollektiv\EasyCreditApi\Client(
                $this->config,
                new \Netzkollektiv\EasyCreditApi\Client\HttpClientFactory(),
                $this->logger
            );

            $this->checkout = new \Netzkollektiv\EasyCreditApi\Checkout(
                $client,
                new \Netzkollektiv\EasyCredit\BackendApi\Storage($this->checkoutSession)
            );
        }
        return $this->checkout;
    }

    public function getMerchantClient()
    {
        return new \Netzkollektiv\EasyCreditApi\Merchant(
            $this->config,
            new \Netzkollektiv\EasyCreditApi\Client\HttpClientFactory(),
            $this->logger
        );
    }

    public function formatPaymentPlan($paymentPlan) {
        $paymentPlan = \json_decode($paymentPlan);
        if (!\is_object($paymentPlan)) {
            return '';
        }

        return \sprintf('%d Raten à %0.2f€ (%d x %0.2f€, %d x %0.2f€)',
            (int)   $paymentPlan->anzahlRaten,
            (float) $paymentPlan->betragRate,
            (int)   $paymentPlan->anzahlRaten - 1,
            (float) $paymentPlan->betragRate,
            1,
            (float) $paymentPlan->betragLetzteRate
        );
    }
}
