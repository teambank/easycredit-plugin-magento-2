<?php
/*
 * (c) NETZKOLLEKTIV GmbH <kontakt@netzkollektiv.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Netzkollektiv\EasyCredit\Plugin;

use Magento\Quote\Api\Data\PaymentInterface;
use Netzkollektiv\EasyCredit\Model\Payment;

class InterceptSaveOrder
{
    public function aroundSavePaymentInformationAndPlaceOrder(
        \Magento\Checkout\Model\PaymentInformationManagement $subject,
        callable $proceed,
        ...$args
    ) {
        $paymentMethod = null;
        foreach ($args as $arg) {
            if ($arg instanceof PaymentInterface) {
                $paymentMethod = $arg;
            }
        }

        if ($paymentMethod === null || $paymentMethod->getMethod() !== Payment::CODE) {
            return $proceed(...$args);
        }
        $subject->savePaymentInformation(...$args);
    }
}
