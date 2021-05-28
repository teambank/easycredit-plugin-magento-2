<?php
/*
 * (c) NETZKOLLEKTIV GmbH <kontakt@netzkollektiv.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Netzkollektiv\EasyCredit\Observer;

use Magento\Framework\Event\Observer;
use Magento\Payment\Observer\AbstractDataAssignObserver;
use Magento\Quote\Api\Data\PaymentInterface;

class PaymentAdditionalDataAssignObserver extends AbstractDataAssignObserver
{
    const FIELD_NAME_INDEX = 'customer_prefix';

    protected $_customerSession;

    public function __construct(
        \Magento\Customer\Model\Session $customerSession
    ) {
        $this->_customerSession = $customerSession;
    }

    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        $data = $this->readDataArgument($observer);

        $additionalData = $data->getData(PaymentInterface::KEY_ADDITIONAL_DATA);
        if (!is_array($additionalData) || !isset($additionalData[self::FIELD_NAME_INDEX])) {
            return; // or throw exception depending on your logic
        }

        $this->_customerSession->setData(
            self::FIELD_NAME_INDEX,
            $additionalData[self::FIELD_NAME_INDEX]
        );
    }
}
