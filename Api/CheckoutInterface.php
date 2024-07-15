<?php
/*
 * (c) NETZKOLLEKTIV GmbH <kontakt@netzkollektiv.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Netzkollektiv\EasyCredit\Api;

use Netzkollektiv\EasyCredit\Api\Data\CheckoutDataInterface;
use Netzkollektiv\EasyCredit\Api\Data\CheckoutRequestInterface;

interface CheckoutInterface
{
    /**
     * @api
     * @param  string $cartId
     * @param \Netzkollektiv\EasyCredit\Api\Data\CheckoutRequestInterface $checkoutData
     * @return \Netzkollektiv\EasyCredit\Api\Data\CheckoutDataInterface
     */
    public function getCheckoutData($cartId, CheckoutRequestInterface $checkoutData);

    /**
     * @api
     * @param  string $cartId
     * @param \Netzkollektiv\EasyCredit\Api\Data\CheckoutRequestInterface $checkoutData
     * @return \Netzkollektiv\EasyCredit\Api\Data\CheckoutDataInterface
     */
    public function start($cartId, CheckoutRequestInterface $checkoutData);
}
