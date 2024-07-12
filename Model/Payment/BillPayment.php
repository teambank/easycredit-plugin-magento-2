<?php

namespace Netzkollektiv\EasyCredit\Model\Payment;

class BillPayment extends AbstractPayment
{
    /**
     * @var string
     */
    public const CODE = 'easycredit_rechnung';

    /**
     * Payment method code
     *
     * @var string
     */
    protected $_code = self::CODE;
}
