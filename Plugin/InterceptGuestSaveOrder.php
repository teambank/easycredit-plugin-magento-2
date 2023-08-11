<?php
/*
 * (c) NETZKOLLEKTIV GmbH <kontakt@netzkollektiv.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Netzkollektiv\EasyCredit\Plugin;

use Magento\Checkout\Model\GuestPaymentInformationManagement;
use Magento\Quote\Api\Data\PaymentInterface;
use Netzkollektiv\EasyCredit\Model\Payment;

class InterceptGuestSaveOrder
{
    public function aroundSavePaymentInformationAndPlaceOrder(
        GuestPaymentInformationManagement $subject,
        callable $proceed,
        ...$args
    ) {
        $paymentMethod = null;
        foreach ($args as $arg) {
            if ($arg instanceof PaymentInterface) {
                $paymentMethod = $arg;
            }
        }

        if (! $paymentMethod instanceof PaymentInterface) {
            return $proceed(...$args);
        }

        if ($paymentMethod->getMethod() !== Payment::CODE) {
            return $proceed(...$args);
        }

        $subject->savePaymentInformation(...$args);
    }
}
