<?php
/*
 * (c) NETZKOLLEKTIV GmbH <kontakt@netzkollektiv.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Netzkollektiv\EasyCredit\BackendApi\Quote;

class ShippingAddress extends Address implements \Netzkollektiv\EasyCreditApi\Rest\ShippingAddressInterface
{
    public function getIsPackstation()
    {
        $street = $this->_address->getStreet();
        if (is_array($street)) {
            $street = implode(' ', $street);
        }
        $street.= $this->getStreetAdditional();
        return stripos($street, 'packstation');
    }
}
