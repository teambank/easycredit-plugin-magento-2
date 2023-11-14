<?php
/*
 * (c) NETZKOLLEKTIV GmbH <kontakt@netzkollektiv.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Netzkollektiv\EasyCredit\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Netzkollektiv\EasyCredit\Logger\Logger;
use Netzkollektiv\EasyCredit\Model\Payment;
use Netzkollektiv\EasyCredit\Service\Authorization as AuthorizationService;

class InstantAuthorization implements ObserverInterface
{
    private AuthorizationService $authorizationService;

    private Logger $logger;

    public function __construct(
        AuthorizationService $authorizationService,
        Logger $logger
    ) {
        $this->authorizationService = $authorizationService;
        $this->logger = $logger;
    }

    public function execute(Observer $observer): void
    {
        $event = $observer->getEvent();

        $order = $event->getData('order');

        if ($order->getPayment()->getMethod() != Payment::CODE) {
            return;
        }

        try {
            $this->authorizationService->authorize($order);
        } catch (\Throwable $e) {
            $this->logger->error($e->getMessage());
        }
    }
}
