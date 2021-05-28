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
    private $errorMessage;

    private $agreement;

    private $prefixValid;

    /**
     * Gets the error message
     *
     * @return string|null Error Message
     */
    public function getErrorMessage()
    {
        return $this->errorMessage;
    }

    /**
     * Sets the error message
     *
     * @param int $message
     * @return $this
     */
    public function setErrorMessage($message)
    {
        $this->errorMessage = $message;
        return $this;
    }

    /**
     * Gets the agreement
     *
     * @return string|null Agreement
     */
    public function getAgreement()
    {
        return $this->agreement;
    }

    /**
     * Sets the agreement
     *
     * @param int $agreement
     * @return $this
     */
    public function setAgreement($agreement)
    {
        $this->agreement = $agreement;
        return $this;
    }

    /**
     * Prefix valid
     *
     * @return boolean
     */
    public function getIsPrefixValid()
    {
        return $this->prefixValid;
    }

    /**
     * Sets the agreement
     *
     * @param int $agreement
     * @return $this
     */
    public function setIsPrefixValid($prefixValid)
    {
        $this->prefixValid = $prefixValid;
        return $this;
    }
}
