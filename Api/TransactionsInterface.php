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
     * @return mixed
     */
    public function getTransaction();

    /**
     * @api
     * @param int $transactionId
     * @param string $status
     * @param string $amount
     * @param string $date
     * @return bool
     */
    public function saveTransaction();
}
