<?php
/*
 * (c) NETZKOLLEKTIV GmbH <kontakt@netzkollektiv.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Netzkollektiv\EasyCredit\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Netzkollektiv\EasyCredit\Helper\Payment as PaymentHelper;
use Netzkollektiv\EasyCredit\Logger\Logger;
use Netzkollektiv\EasyCredit\Service\Authorization as AuthorizationService;

class InstantAuthorization implements ObserverInterface
{
    private AuthorizationService $authorizationService;

    private Logger $logger;

    private PaymentHelper $paymentHelper;

    public function __construct(
        AuthorizationService $authorizationService,
        Logger $logger,
        PaymentHelper $paymentHelper
    ) {
        $this->authorizationService = $authorizationService;
        $this->logger = $logger;
        $this->paymentHelper = $paymentHelper;
    }

    public function execute(Observer $observer): void
    {
        $event = $observer->getEvent();

        $order = $event->getData('order');

        if (! $this->paymentHelper->isSelected($order->getPayment())) {
            return;
        }

        try {
            $this->authorizationService->authorize($order);
        } catch (\Throwable $e) {
            $this->logger->error($e->getMessage());
        }
    }
}
