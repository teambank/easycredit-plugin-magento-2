<?php
/*
 * (c) NETZKOLLEKTIV GmbH <kontakt@netzkollektiv.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Netzkollektiv\EasyCredit\BackendApi\Quote;

use Magento\Customer\Model\Session as CustomerSession;
use Netzkollektiv\EasyCredit\Helper\Data as EasyCreditHelper;
use Teambank\RatenkaufByEasyCreditApiV3 as Api;
use Teambank\RatenkaufByEasyCreditApiV3\Integration\Util\PrefixConverter;

class CustomerBuilder
{
    private $customerSession;
    private $easyCreditHelper;

    public function __construct(
        CustomerSession $customerSession,
        EasyCreditHelper $easyCreditHelper,
        PrefixConverter $prefixConverter
    ) {
        $this->customerSession = $customerSession;
        $this->easyCreditHelper = $easyCreditHelper;
        $this->prefixConverter = $prefixConverter;
    }

    public function build($quote)
    {
        if ($this->customerSession->isLoggedIn()) {
            $customer = $quote->getCustomer();
        } else {
            $customer = $quote->getShippingAddress();
        }

        return new Api\Model\Customer(
            [
            'gender' => $this->prefixConverter->convert($customer->getPrefix()),
            'firstName' => $customer->getFirstname(),
            'lastName' => $customer->getLastname(),
            'birthDate' => $customer->getDob(),
            'contact' => new Api\Model\Contact(
                [
                'email' => $quote->getBillingAddress()->getEmail()
                ]
            ),
            'companyName' => $quote->getShippingAddress()->getCompany()
            ]
        );        
    }
}
