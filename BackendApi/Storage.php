<?php
/*
 * (c) NETZKOLLEKTIV GmbH <kontakt@netzkollektiv.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Netzkollektiv\EasyCredit\BackendApi;

class Storage implements \Netzkollektiv\EasyCreditApi\StorageInterface
{
    protected $_payment;

    public function __construct(
        \Magento\Quote\Model\Quote\Payment $payment
    ) {
        $this->_payment = $payment;
    }

    public function set($key, $value)
    {
        $this->_payment->setAdditionalInformation($key, $value);
        return $this;
    }

    public function get($key)
    {
        return $this->_payment->getAdditionalInformation($key);
    }

    public function clear()
    {
        $this->_payment->unsAdditionalInformation()->save();
    }
}
