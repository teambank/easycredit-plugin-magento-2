<?php
/*
 * (c) NETZKOLLEKTIV GmbH <kontakt@netzkollektiv.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Netzkollektiv\EasyCredit\Api;

interface VerifyCredentialsInterface
{
    /**
     * @api
     * @param string|null $apiKey
     * @param string|null $apiToken
     * @param string|null $apiSignature
     * @return bool
     */
    public function verifyCredentials(?string $apiKey, ?string $apiToken, ?string $apiSignature);
}
