<?php
/*
 * (c) NETZKOLLEKTIV GmbH <kontakt@netzkollektiv.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Netzkollektiv\EasyCredit\Api\Data;

interface CheckoutRequestInterface
{
    /**
     * Gets the cart id
     *
     * @return string
     */
    public function getCartId(): ?string;

    /**
     * Sets the cart id
     * @return self
     */
    public function setCartId(string $cartId): self;

    /**
     * Gets the express flag
     *
     * @return int
     */
    public function getExpress(): ?int;

    /**
     * Sets the express flag
     * @return self
     */
    public function setExpress(int $flag): self;

    /**
     * Gets the payment type
     * @return string
     */
    public function getPaymentType(): ?string;

    /**
     * Gets the payment type
     *
     * @return self
     */
    public function setPaymentType(string $paymentType): self;

    /**
     * Gets the number of installments
     *
     * @return string
     */
    public function getNumberOfInstallments(): ?string;

    /**
     * Sets the number of installments
     * @return self
     */
    public function setNumberOfInstallments(string $num): self;
}
