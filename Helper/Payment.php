<?php
/*
 * (c) NETZKOLLEKTIV GmbH <kontakt@netzkollektiv.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Netzkollektiv\EasyCredit\Helper;

use Magento\Framework\Api\ExtensibleDataInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Quote\Api\Data\PaymentInterface;

use Magento\Sales\Api\Data\OrderPaymentInterface;
use Netzkollektiv\EasyCredit\Model\Payment as EasyCreditPayment;

class Payment extends AbstractHelper
{
    private $paymentConfig = null;

    public function __construct(
        \Magento\Payment\Model\Config $paymentConfig
    ) {
        $this->paymentConfig = $paymentConfig;
    }

    private $typeMapping = [
        'INSTALLMENT' => EasyCreditPayment\InstallmentPayment::CODE,
        'BILL' => EasyCreditPayment\BillPayment::CODE,
    ];

    public function isSelected(ExtensibleDataInterface $paymentMethod)
    {
        if (
            ! $paymentMethod instanceof OrderPaymentInterface
            && ! $paymentMethod instanceof PaymentInterface
        ) {
            return false;
        }

        return $this->isMethodSelected($paymentMethod->getMethod());
    }

    public function isMethodSelected(string $method)
    {
        if (
            EasyCreditPayment\BillPayment::CODE === $method ||
            EasyCreditPayment\InstallmentPayment::CODE === $method
        ) {
            return true;
        }
        return false;
    }

    public function getMethodByType(string $type)
    {
        $type = str_replace('_PAYMENT', '', $type);
        if (! isset($this->typeMapping[$type])) {
            throw new \Exception('payment type ' . $type . ' does not exist');
        }
        return $this->typeMapping[$type];
    }

    public function getTypeByMethod(string $method)
    {
        $typeMapping = array_flip($this->typeMapping);
        if (! isset($typeMapping[$method])) {
            throw new \Exception('method ' . $method . ' does not exist');
        }
        return $typeMapping[$method];
    }

    public function getAvailableMethods()
    {
        return array_filter($this->paymentConfig->getActiveMethods(), function ($method) {
            return $method instanceof EasyCreditPayment\BillPayment ||
                $method instanceof EasyCreditPayment\InstallmentPayment;
        });
    }

    public function getAvailableMethodTypes()
    {
        return array_map(function ($paymentMethod) {
            return $this->getTypeByMethod($paymentMethod::CODE);
        }, $this->getAvailableMethods());
    }
}
