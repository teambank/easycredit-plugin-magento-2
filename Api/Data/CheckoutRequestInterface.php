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
     * @return string
     */
    public function getCartId(): ?string;

    /**
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
     * @return string
     */
    public function getPaymentType(): ?string;

    /**
     * @return self
     */
    public function setPaymentType(string $paymentType): self;

    /**
     * @return string
     */
    public function getNumberOfInstallments(): ?string;

    /**
     * @return self
     */
    public function setNumberOfInstallments(string $num): self;
}
