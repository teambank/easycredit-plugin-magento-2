<?php

namespace Netzkollektiv\EasyCredit\Model\Payment;

class LegacyPayment extends AbstractPayment
{
    /**
     * @var string
     */
    public const CODE = 'easycredit';

    /**
     * Payment method code
     *
     * @var string
     */
    protected $_code = self::CODE;
}
