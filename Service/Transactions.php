<?php
/*
 * (c) NETZKOLLEKTIV GmbH <kontakt@netzkollektiv.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Netzkollektiv\EasyCredit\Service;

use Teambank\RatenkaufByEasyCreditApiV3\Model\CaptureRequest;
use Teambank\RatenkaufByEasyCreditApiV3\Model\RefundRequest;
use Teambank\RatenkaufByEasyCreditApiV3\ApiException;

use Netzkollektiv\EasyCredit\Api\TransactionsInterface;
use Netzkollektiv\EasyCredit\Helper\Data as EasyCreditHelper;

class Transactions implements TransactionsInterface
{

    /**
     * @var EasyCreditHelper
     */
    private $easyCreditHelper;

    /**
     * @var \Magento\Framework\App\ResponseInterface
     */
    private $response;

    /**
     * @var \Magento\Framework\Webapi\Rest\Request
     */
    private $request;

    public function __construct(
        EasyCreditHelper $easyCreditHelper,
        \Magento\Framework\Webapi\Rest\Request $request,
        \Magento\Framework\App\ResponseInterface $response
    ) {
        $this->request = $request;
        $this->response = $response;
        $this->easyCreditHelper = $easyCreditHelper; 
    }

    private function sendJsonResponseFromException(ApiException $response) : void
    {
        $this->sendJsonResponse((string) $response->getResponseBody(), $response->getCode());
    }

    private function sendJsonResponse(string $body, int $statusCode = 200) : void
    {
        $this->response->setHeader('Content-Type', 'application/json', true)
            ->setBody($body)
            ->setHttpResponseCode($statusCode);
        $this->response->sendResponse();
        exit;
    }

    /**
     * @api
     */
    public function getTransactions()
    {
        try {
            $transactionIds = $this->request->getParam('ids');

            $response = $this->easyCreditHelper
                ->getTransactionApi()
                ->apiMerchantV3TransactionGet(null, null,  null, 100, null, null, null, null, ['tId' => $transactionIds]);
            $this->sendJsonResponse($response);
        } catch (ApiException $e) {
            $this->sendJsonResponseFromException($e);
        } catch (\Throwable $e) {
            $this->sendJsonResponse(
                json_encode(
                    [
                    'error' => $e->getMessage()
                    ], 500
                )
            );
        }
    }

    /**
     * @api
     */
    public function getTransaction($transactionId)
    {
        try {
            $response = $this->easyCreditHelper
                ->getTransactionApi()
                ->apiMerchantV3TransactionTransactionIdGet($transactionId);
            $this->sendJsonResponse($response);
        } catch (ApiException $e) {
            $this->sendJsonResponseFromException($e);
        } catch (\Throwable $e) {
            $this->sendJsonResponse(
                json_encode(
                    [
                    'error' => $e->getMessage()
                    ], 500
                )
            );
        }
    }

    /**
     * @api
     */
    public function captureTransaction($transactionId)
    {
        try {
            $bodyParams = $this->request->getBodyParams();;

            $this->easyCreditHelper
                ->getTransactionApi()
                ->apiMerchantV3TransactionTransactionIdCapturePost(
                    $transactionId,
                    new CaptureRequest(['trackingNumber' => $bodyParams['trackingNumber'] ?? null])
                );
        } catch (ApiException $e) {
            $this->sendJsonResponseFromException($e);
        } catch (\Throwable $e) {
            $this->sendJsonResponse(
                json_encode(
                    [
                    'error' => $e->getMessage()
                    ], 500
                )
            );
        }
    }

    /**
     * @api
     */
    public function refundTransaction($transactionId)
    {
        try {
            $bodyParams = $this->request->getBodyParams();;

            $this->easyCreditHelper
                ->getTransactionApi()
                ->apiMerchantV3TransactionTransactionIdRefundPost(
                    $transactionId,
                    new RefundRequest(['value' => $bodyParams['value']])
                );
        } catch (ApiException $e) {
            $this->sendJsonResponseFromException($e);
        } catch (\Throwable $e) {
            $this->sendJsonResponse(
                json_encode(
                    [
                    'error' => $e->getMessage()
                    ], 500
                )
            );
        }
    }
}
