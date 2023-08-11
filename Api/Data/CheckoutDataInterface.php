<?php
/*
 * (c) NETZKOLLEKTIV GmbH <kontakt@netzkollektiv.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Netzkollektiv\EasyCredit\Api\Data;

interface CheckoutDataInterface
{
    /**
     * Gets the error message
     *
     * @return string
     */
    public function getErrorMessage(): ?string;

    /**
     * Sets the error message
     * @return self
     */
    public function setErrorMessage(string $message): self;

    /**
     * Gets the redirect url
     *
     * @return string
     */
    public function getRedirectUrl(): ?string;

    /**
     * Sets the redirect url
     * @return self
     */
    public function setRedirectUrl(string $url): self;
}
