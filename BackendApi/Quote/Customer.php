<?php
/*
 * (c) NETZKOLLEKTIV GmbH <kontakt@netzkollektiv.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Netzkollektiv\EasyCredit\BackendApi\Quote;

use Magento\Customer\Model\Session as CustomerSession;
use Magento\Sales\Model\ResourceModel\Order\Collection as SalesOrderCollection;

class Customer implements \Netzkollektiv\EasyCreditApi\Rest\CustomerInterface
{
    protected $_customer = null;
    protected $_billingAddress = null;
    protected $_customerSession = null;
    protected $_salesOrderCollection = null;

    public function __construct(
        CustomerSession $customerSession,
        SalesOrderCollection $salesOrderCollection,
        $customer,
        $billingAddress,
        $shippingAddress,
        \Netzkollektiv\EasyCredit\Helper\Data $easyCreditHelper
    ) {
        $this->_customerSession = $customerSession;
        $this->_salesOrderCollection = $salesOrderCollection;
        $this->_customer = $customer;
        $this->_billingAddress = $billingAddress;
        $this->_shippingAddress = $shippingAddress;
        $this->_easyCreditHelper = $easyCreditHelper;
    }

    public function getPrefix()
    {
        $prefix = $this->_customerSession->getCustomerPrefix();
        if ($this->_easyCreditHelper->getCheckout()->isPrefixValid($prefix)) {
            return $prefix;
        }

        if (!$this->isLoggedIn()) {
            return $this->_shippingAddress->getPrefix();
        }
        return $this->_customer->getPrefix();
    }

    public function getFirstname()
    {
        if (!$this->isLoggedIn()) {
            return $this->_shippingAddress->getFirstname();
        }
        return $this->_customer->getFirstname();
    }

    public function getLastname()
    {
        if (!$this->isLoggedIn()) {
            return $this->_shippingAddress->getLastname();
        }
        return $this->_customer->getLastname();
    }

    public function getCompany()
    {
        return $this->_shippingAddress->getCompany();
    }

    public function getEmail()
    {
        return $this->_billingAddress->getEmail();
    }

    public function getDob()
    {
        if (!$this->isLoggedIn()) {
            return null;
        }
        return $this->_customer->getDob();
    }

    public function getTelephone()
    {
        return $this->_shippingAddress->getTelephone();
    }

    public function isLoggedIn()
    {
        return $this->_customerSession->isLoggedIn();
    }

    public function getCreatedAt()
    {
        return $this->_customer->getCreatedAt();
    }

    public function getOrderCount()
    {
        if (!$this->isLoggedIn()) {
            return 0;
        }

        return $this->_salesOrderCollection
            ->addFieldToSelect('*')
            ->addFieldToFilter('customer_id', $this->_customer->getId())
            ->count();
    }
}
