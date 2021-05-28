<?php
/*
 * (c) NETZKOLLEKTIV GmbH <kontakt@netzkollektiv.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Netzkollektiv\EasyCredit\Service\V1;

use Netzkollektiv\EasyCredit\Api\VerifyCredentialsInterface;
use Netzkollektiv\EasyCredit\Helper\Data;

class VerifyCredentials implements VerifyCredentialsInterface
{
    public function __construct(
        Data $easyCreditHelper
    ) {
        $this->easyCreditCheckout = $easyCreditHelper->getCheckout();
    }

    public function verifyCredentials($apiKey, $apiToken)
    {
        return $this->easyCreditCheckout
            ->verifyCredentials($apiKey, $apiToken);
    }
}
