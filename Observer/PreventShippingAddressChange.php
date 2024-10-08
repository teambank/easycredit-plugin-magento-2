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
use Magento\Sales\Model\Order\Address;
use Netzkollektiv\EasyCredit\Helper\Payment as PaymentHelper;

class PreventShippingAddressChange implements ObserverInterface
{
    protected PaymentHelper $paymentHelper;

    public function __construct(
        PaymentHelper $paymentHelper
    ) {
        $this->paymentHelper = $paymentHelper;
    }

    public function execute(Observer $observer)
    {
        /**
         * @var Address $address
         */
        $address = $observer->getEvent()->getData('address');
        if ($address->getAddressType() != 'shipping') {
            return;
        }

        if (! $this->paymentHelper->isSelected($address->getOrder()->getPayment())) {
            return;
        }

        throw new LocalizedException(
            __(
                'Die Lieferadresse kann bei mit easyCredit bezahlten Bestellungen 
                nicht im Nachhinein geändert werden. Bitte stornieren Sie die Bestellung und Zahlung 
                hierfür und legen Sie eine neue Bestellung an.'
            )
        );
    }
}
