<?php
/*
 * (c) NETZKOLLEKTIV GmbH <kontakt@netzkollektiv.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Netzkollektiv\EasyCredit\Api;

interface TransactionsInterface
{
    /**
     * @api
     * @return mixed
     */
    public function getTransactions();

    /**
     * @api
     * @param  string $transactionId
     * @return mixed
     */
    public function getTransaction($transactionId);

    /**
     * @api
     * @param  string $transactionId
     * @return mixed
     */
    public function captureTransaction($transactionId);

    /**
     * @api
     * @param  string $transactionId
     * @return mixed
     */
    public function refundTransaction($transactionId);
}
