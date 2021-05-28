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
        $address = $observer->getEvent()->getAddress();
        if ($address->getAddressType() == 'shipping'
            && \Netzkollektiv\EasyCredit\Model\Payment::CODE == $address->getOrder()->getPayment()->getMethod()
        ) {
            throw new \Magento\Framework\Exception\LocalizedException(__(
                'Die Lieferadresse kann bei mit ratenkauf by easyCredit bezahlten Bestellungen 
                nicht im Nachhinein geändert werden. Bitte stornieren Sie die Bestellung und Zahlung 
                hierfür und legen Sie eine neue Bestellung an.'
            ));
        }
    }
}
