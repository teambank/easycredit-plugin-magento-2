<?php
/*
 * (c) NETZKOLLEKTIV GmbH <kontakt@netzkollektiv.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Netzkollektiv\EasyCredit\BackendApi;

use Magento\Quote\Model\Quote\Payment;
use Netzkollektiv\EasyCredit\Logger\Logger;

use Teambank\RatenkaufByEasyCreditApiV3 as Api;

class Storage implements Api\Integration\StorageInterface
{
    private Payment $payment;

    private Logger $logger;

    public function __construct(
        Payment $payment,
        Logger $logger
    ) {
        $this->payment = $payment;
        $this->logger = $logger;
    }

    public function set($key, $value)
    {
        $this->logger->debug('set(' . $key . ', ' . $value . ')');
        $this->payment->setAdditionalInformation($key, $value);
        return $this;
    }

    public function get($key)
    {
        $this->logger->debug('get(' . $key . ') => ' . $this->payment->getAdditionalInformation($key));
        return $this->payment->getAdditionalInformation($key);
    }

    public function clear(): void
    {
        $this->logger->debug('clear');
        $this->payment->unsAdditionalInformation()->save(); // @phpstan-ignore-line
    }
}
