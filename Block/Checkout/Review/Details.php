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
    private ?\Magento\Quote\Model\Quote\Address $_address = null;

    /**
     * Return review shipping address
     *
     * @return Address|null
     */
    public function getAddress(): ?\Magento\Quote\Model\Quote\Address
    {

        return $this->_address;
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
