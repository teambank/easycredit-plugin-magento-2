<?php
/*
 * (c) NETZKOLLEKTIV GmbH <kontakt@netzkollektiv.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Netzkollektiv\EasyCredit\Api;

use Netzkollektiv\EasyCredit\Api\Data\CheckoutDataInterface;

class CheckoutData implements CheckoutDataInterface
{
    private ?string $errorMessage = null;

    private ?string $redirectUrl = null;

    /**
     * @return string
     */
    public function getErrorMessage(): ?string
    {
        return $this->errorMessage;
    }

    /**
     * @return self
     */
    public function setErrorMessage(string $message): self
    {
        $this->errorMessage = $message;
        return $this;
    }

    /**
     * @return string
     */
    public function getRedirectUrl(): ?string
    {
        return $this->redirectUrl;
    }

    /**
     * @return self
     */
    public function setRedirectUrl(string $url): self
    {
        $this->redirectUrl = $url;
        return $this;
    }
}
