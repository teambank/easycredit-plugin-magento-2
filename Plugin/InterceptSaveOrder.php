<?php
/*
 * (c) NETZKOLLEKTIV GmbH <kontakt@netzkollektiv.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Netzkollektiv\EasyCredit\Plugin;

use Magento\Checkout\Model\PaymentInformationManagement;
use Magento\Quote\Api\Data\PaymentInterface;
use Netzkollektiv\EasyCredit\Helper\Payment as PaymentHelper;

class InterceptSaveOrder
{
    private PaymentHelper $paymentHelper;

    public function __construct(
        PaymentHelper $paymentHelper
    ) {
        $this->paymentHelper = $paymentHelper;
    }

    public function aroundSavePaymentInformationAndPlaceOrder(
        PaymentInformationManagement $subject,
        callable $proceed,
        ...$args
    ) {
        $paymentMethod = $this->getPaymentArg($args);
        if ($this->paymentHelper->isSelected($paymentMethod)) {
            return $subject->savePaymentInformation(...$args);
        }
        return $proceed(...$args);

    }

    private function getPaymentArg($args) {
        foreach ($args as $arg) {
            if ($arg instanceof PaymentInterface) {
                return $arg;
            }
        }
        return null;
    }
}
