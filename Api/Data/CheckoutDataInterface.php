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
     * @return string|null Error Message
     */
    public function getErrorMessage();

    /**
     * Sets the error message
     *
     * @param int $message
     * @return $this
     */
    public function setErrorMessage($message);

    /**
     * Gets the agreement
     *
     * @return string|null Agreement
     */
    public function getAgreement();

    /**
     * Sets the agreement
     *
     * @param int $agreement
     * @return $this
     */
    public function setAgreement($agreement);

    /**
     * Prefix valid
     *
     * @return boolean
     */
    public function getIsPrefixValid();

    /**
     * Prefix valid
     *
     * @param boolean $prefixValid
     * @return $this
     */
    public function setIsPrefixValid($prefixValid);
}
