<?php
/*
 * (c) NETZKOLLEKTIV GmbH <kontakt@netzkollektiv.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Netzkollektiv\EasyCredit\BackendApi\Quote;

use Magento\Customer\Model\Session as CustomerSession;
use Teambank\RatenkaufByEasyCreditApiV3 as Api;
use Teambank\RatenkaufByEasyCreditApiV3\Integration\Util\PrefixConverter;
use Teambank\RatenkaufByEasyCreditApiV3\Model\Customer;

class CustomerBuilder
{
    private CustomerSession $customerSession;

    private PrefixConverter $prefixConverter;

    public function __construct(
        CustomerSession $customerSession,
        PrefixConverter $prefixConverter
    ) {
        $this->customerSession = $customerSession;
        $this->prefixConverter = $prefixConverter;
    }

    public function build($quote): Customer
    {
        $customer = $this->customerSession->isLoggedIn() ? $quote->getCustomer() : $quote->getShippingAddress();

        return new Api\Model\Customer(
            [
                'gender' => $this->prefixConverter->convert($customer->getPrefix()),
                'firstName' => $customer->getFirstname(),
                'lastName' => $customer->getLastname(),
                'birthDate' => $customer->getDob(),
                'contact' => $quote->getBillingAddress()->getEmail() ? new Api\Model\Contact(
                    [
                        'email' => $quote->getBillingAddress()->getEmail(),
                    ]
                ) : null,
                'companyName' => $quote->getShippingAddress()->getCompany(),
            ]
        );
    }
}
