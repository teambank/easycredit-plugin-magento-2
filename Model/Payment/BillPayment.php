<?php
namespace Netzkollektiv\EasyCredit\Model\Payment;

class BillPayment extends AbstractPayment {
    /**
     * @var string
     */
    public const CODE = 'easycredit_bill_payment';

    /**
     * Payment method code
     *
     * @var string
     */
    protected $_code = self::CODE;
}