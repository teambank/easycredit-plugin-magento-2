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
     * @return string
     */
    public function getErrorMessage(): ?string;

    /**
     * @return self
     */
    public function setErrorMessage(string $message): self;

    /**
     * @return string
     */
    public function getRedirectUrl(): ?string;

    /**
     * @return self
     */
    public function setRedirectUrl(string $url): self;
}
