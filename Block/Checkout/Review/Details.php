<?php
/*
 * (c) NETZKOLLEKTIV GmbH <kontakt@netzkollektiv.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Netzkollektiv\EasyCredit\Block\Checkout\Review;

use Magento\Sales\Model\Order\Address;

/**
 * EasyCredit Review block
 */
class Details extends \Magento\Checkout\Block\Cart\Totals
{
    /**
     * @var Address
     */
    protected $_address;

    /**
     * Return review shipping address
     *
     * @return Address
     */
    public function getAddress()
    {
        if (empty($this->_address)) {
            $this->_address = $this->getQuote()->getShippingAddress();
        }
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
