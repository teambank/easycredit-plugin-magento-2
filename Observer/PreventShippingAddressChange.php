<?php
/*
 * (c) NETZKOLLEKTIV GmbH <kontakt@netzkollektiv.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Netzkollektiv\EasyCredit\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class PreventShippingAddressChange implements ObserverInterface
{
    public function execute(Observer $observer)
    {
        /**
         * @var \Magento\Sales\Model\Order\Address $address
         */
        $address = $observer->getEvent()->getData('address');

        if ($address->getAddressType() == 'shipping'
            && $address->getOrder()->getPayment()
            && \Netzkollektiv\EasyCredit\Model\Payment::CODE == $address->getOrder()->getPayment()->getMethod()
        ) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __(
                    'Die Lieferadresse kann bei mit easyCredit-Ratenkauf bezahlten Bestellungen 
                nicht im Nachhinein geändert werden. Bitte stornieren Sie die Bestellung und Zahlung 
                hierfür und legen Sie eine neue Bestellung an.'
                )
            );
        }
    }
}
