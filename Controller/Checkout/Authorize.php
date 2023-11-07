<?php
/*
 * (c) NETZKOLLEKTIV GmbH <kontakt@netzkollektiv.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Netzkollektiv\EasyCredit\Controller\Checkout;

use Magento\Checkout\Model\Session;
use Magento\Customer\Model\Url;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Email\Sender\OrderSender;
use Magento\Sales\Model\OrderFactory;
use Magento\Sales\Model\OrderRepository;

use Netzkollektiv\EasyCredit\Helper\Data as EasyCreditHelper;
use Netzkollektiv\EasyCredit\Logger\Logger;
use Netzkollektiv\EasyCredit\Service\Authorization as AuthorizationService;

class Authorize extends AbstractController
{
    private OrderFactory $orderFactory;

    private ScopeConfigInterface $scopeConfig;

    private EasyCreditHelper $easyCreditHelper;

    private OrderRepository $orderRepository;

    private OrderSender $orderSender;

    private AuthorizationService $authorizationService;

    private Logger $logger;

    public function __construct(
        Context $context,
        Session $checkoutSession,
        Url $customerUrl,
        OrderFactory $orderFactory,
        ScopeConfigInterface $scopeConfig,
        EasyCreditHelper $easyCreditHelper,
        OrderRepository $orderRepository,
        OrderSender $orderSender,
        AuthorizationService $authorizationService,
        Logger $logger
    ) {
        $this->orderFactory = $orderFactory;
        $this->scopeConfig = $scopeConfig;
        $this->easyCreditHelper = $easyCreditHelper;
        $this->orderSender = $orderSender;
        $this->orderRepository = $orderRepository;
        $this->authorizationService = $authorizationService;

        $this->logger = $logger;

        parent::__construct($context, $checkoutSession, $customerUrl);
    }

    /**
     * Dispatch request
     * @return void
     */
    public function execute()
    {
        $secToken = $this->getRequest()->getParam('secToken');
        $txId = $this->getRequest()->getParam('transactionId');
        $incrementId = $this->getRequest()->getParam('orderId');

        if (! $txId) {
            throw new \Exception('no transaction ID provided');
        }

        $order = $this->orderFactory->create()->loadByIncrementId($incrementId);
        if (! $order->getId()) {
            throw new \Exception('order not found');
        }
        if ($order->getState() != Order::STATE_PAYMENT_REVIEW) {
            throw new \Exception('order status not valid for authorization');
        }

        $payment = $order->getPayment();
        if (! isset($payment->getAdditionalInformation()['sec_token'])
            && $secToken !== $payment->getAdditionalInformation()['sec_token']
        ) {
            throw new \Exception('secToken not valid');
        }

        try {
            $this->authorizationService->authorize($order);
        } catch (\Throwable $e) {
            $this->logger->error($e->getMessage());
            throw new \Exception('payment status could not be set, please check the logs');
        }
    }
}
