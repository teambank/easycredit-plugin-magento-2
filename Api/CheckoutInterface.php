<?php
/*
 * (c) NETZKOLLEKTIV GmbH <kontakt@netzkollektiv.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Netzkollektiv\EasyCredit\Api;

interface CheckoutInterface
{

    /**
     * @api
     * @param string $prefix
     * @return bool
     */
    public function isPrefixValid($prefix);

    /**
     * @api
     * @param string $cartId
     * @return \Netzkollektiv\EasyCredit\Api\Data\CheckoutDataInterface
     */
    public function getCheckoutData($cartId);
}
