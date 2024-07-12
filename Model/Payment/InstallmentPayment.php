<?php

namespace Netzkollektiv\EasyCredit\Model\Payment;

class InstallmentPayment extends AbstractPayment
{
    /**
     * @var string
     */
    public const CODE = 'easycredit_ratenkauf';

    /**
     * Payment method code
     *
     * @var string
     */
    protected $_code = self::CODE;
}
