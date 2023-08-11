<?php
/*
 * (c) NETZKOLLEKTIV GmbH <kontakt@netzkollektiv.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Netzkollektiv\EasyCredit\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Api\Data\OrderPaymentInterface;
use Magento\Sales\Model\Order\Address;
use Netzkollektiv\EasyCredit\Model\Payment;

class PreventShippingAddressChange implements ObserverInterface
{
    public function execute(Observer $observer)
    {
        /**
         * @var Address $address
         */
        $address = $observer->getEvent()->getData('address');
        if ($address->getAddressType() != 'shipping') {
            return;
        }

        if (! $address->getOrder()->getPayment() instanceof OrderPaymentInterface) {
            return;
        }

        if (Payment::CODE != $address->getOrder()->getPayment()->getMethod()) {
            return;
        }

        throw new LocalizedException(
            __(
                'Die Lieferadresse kann bei mit easyCredit-Ratenkauf bezahlten Bestellungen 
                nicht im Nachhinein geändert werden. Bitte stornieren Sie die Bestellung und Zahlung 
                hierfür und legen Sie eine neue Bestellung an.'
            )
        );
    }
}
