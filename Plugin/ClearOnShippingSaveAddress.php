<?php
/*
 * (c) NETZKOLLEKTIV GmbH <kontakt@netzkollektiv.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Netzkollektiv\EasyCredit\Plugin;

use Magento\Checkout\Api\ShippingInformationManagementInterface;
use Netzkollektiv\EasyCredit\Helper\Data as EasyCreditHelper;

class ClearOnShippingSaveAddress
{
    private EasyCreditHelper $easyCreditHelper;

    public function __construct(EasyCreditHelper $easyCreditHelper)
    {
        $this->easyCreditHelper = $easyCreditHelper;
    }

    public function beforeSaveAddressInformation(
        ShippingInformationManagementInterface $subject
    ) {
        $this->easyCreditHelper->getCheckout()->clear();
    }
}
