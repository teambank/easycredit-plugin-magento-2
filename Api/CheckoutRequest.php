<?php
/*
 * (c) NETZKOLLEKTIV GmbH <kontakt@netzkollektiv.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Netzkollektiv\EasyCredit\Api;

use Netzkollektiv\EasyCredit\Api\Data\CheckoutRequestInterface;

class CheckoutRequest implements CheckoutRequestInterface
{
    private $cartId;
    private $express = false;
    private $paymentType;
    private $numberOfInstallments;

    public function getCartId(): ?string
    {
        return $this->cartId;
    }

    public function setCartId(string $cartId): CheckoutRequestInterface
    {
        $this->cartId = $cartId;
        return $this;
    }

    public function getExpress(): ?int
    {
        return $this->express;
    }

    public function setExpress(int $flag): CheckoutRequestInterface
    {
        $this->express = $flag;
        return $this;
    }

    public function getPaymentType(): ?string
    {
        return $this->paymentType;
    }

    public function setPaymentType(string $paymentType): CheckoutRequestInterface
    {
        $this->paymentType = $paymentType;
        return $this;
    }

    public function getNumberOfInstallments(): ?string
    {
        return $this->numberOfInstallments;
    }

    public function setNumberOfInstallments(string $num): CheckoutRequestInterface
    {
        $this->numberOfInstallments = $num;
        return $this;
    }
}
