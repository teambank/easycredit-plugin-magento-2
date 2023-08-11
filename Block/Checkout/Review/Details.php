<?php
/*
 * (c) NETZKOLLEKTIV GmbH <kontakt@netzkollektiv.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Netzkollektiv\EasyCredit\Block\Checkout\Review;

use Magento\Checkout\Block\Cart\Totals;
use Magento\Sales\Model\Order\Address;

/**
 * EasyCredit Review block
 */
class Details extends Totals
{
    /**
     * @var null
     */
    private const ADDRESS = null;

    /**
     * Return review shipping address
     *
     * @return Address|null
     */
    public function getAddress(): ?\Magento\Quote\Model\Quote\Address
    {
        return self::ADDRESS;
    }

    /**
     * Return review quote totals
     *
     * @return array
     */
    public function getTotals()
    {
        return $this->getQuote()->getTotals();
    }
}
