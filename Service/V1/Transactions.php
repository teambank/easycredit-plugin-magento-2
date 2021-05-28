<?php
/*
 * (c) NETZKOLLEKTIV GmbH <kontakt@netzkollektiv.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Netzkollektiv\EasyCredit\Service\V1;

use Netzkollektiv\EasyCredit\Api\TransactionsInterface;

class Transactions implements TransactionsInterface
{
    protected $merchantClient;

    public function __construct(
        \Netzkollektiv\EasyCredit\Helper\Data $easyCreditHelper,
        \Magento\Framework\Webapi\Rest\Request $request
    ) {
        $this->request = $request;
        $this->merchantClient = $easyCreditHelper->getMerchantClient();
    }

    /**
     * @api
     * @return mixed
     */
    public function getTransactions()
    {
        $transactions = [];
        foreach ($this->merchantClient->searchTransactions() as $transaction) {
            $transactions[] = (array)$transaction;
        }
        return $transactions;
    }

    /**
     * @api
     * @return mixed
     */
    public function getTransaction()
    {
        $transactionId = $this->request->getParam('id');

        foreach ($this->merchantClient->searchTransactions() as $transaction) {
            if ($transactionId == $transaction->vorgangskennungFachlich) {
                return [(array)$transaction];
            }
        }
    }

    /**
     * @api
     * @return boolean
     */
    public function saveTransaction()
    {
        $client = $this->merchantClient;

        $params = json_decode($this->request->getContent());

        switch ($params->status) {
            case "LIEFERUNG":
                $client->confirmShipment($params->id);
                break;
            case "WIDERRUF_VOLLSTAENDIG":
            case "WIDERRUF_TEILWEISE":
            case "RUECKGABE_GARANTIE_GEWAEHRLEISTUNG":
            case "MINDERUNG_GARANTIE_GEWAEHRLEISTUNG":
                $client->cancelOrder(
                    $params->id,
                    $params->status,
                    \DateTime::createFromFormat('Y-d-m', $params->date),
                    $params->amount
                );
                break;
        }
        return true;
    }
}
