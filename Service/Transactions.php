<?php
/*
 * (c) NETZKOLLEKTIV GmbH <kontakt@netzkollektiv.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Netzkollektiv\EasyCredit\Service;

use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Webapi\Rest\Request;
use Netzkollektiv\EasyCredit\Api\TransactionsInterface;
use Netzkollektiv\EasyCredit\Helper\Data as EasyCreditHelper;
use Teambank\RatenkaufByEasyCreditApiV3\ApiException;

use Teambank\RatenkaufByEasyCreditApiV3\Model\CaptureRequest;
use Teambank\RatenkaufByEasyCreditApiV3\Model\RefundRequest;

class Transactions implements TransactionsInterface
{
    private EasyCreditHelper $easyCreditHelper;

    private ResponseInterface $response;

    private Request $request;

    public function __construct(
        EasyCreditHelper $easyCreditHelper,
        Request $request,
        ResponseInterface $response
    ) {
        $this->request = $request;
        $this->response = $response;
        $this->easyCreditHelper = $easyCreditHelper;
    }

    /**
     * @return never
     */
    private function sendJsonResponseFromException(ApiException $response): void
    {
        $this->sendJsonResponse((string) $response->getResponseBody(), $response->getCode());
    }

    /**
     * @return never
     */
    private function sendJsonResponse(string $body, int $statusCode = 200): void
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
    public function getTransactions(): void
    {
        try {
            $transactionIds = $this->request->getParam('ids');

            $response = $this->easyCreditHelper
                ->getTransactionApi()
                ->apiMerchantV3TransactionGet(null, null, null, 100, null, null, null, null, [
                    'tId' => $transactionIds,
                ]);
            $this->sendJsonResponse($response);
        } catch (ApiException $apiException) {
            $this->sendJsonResponseFromException($apiException);
        } catch (\Throwable $throwable) {
            $this->sendJsonResponse(
                json_encode(
                    [
                        'error' => $throwable->getMessage(),
                    ],
                    500
                )
            );
        }
    }

    /**
     * @api
     */
    public function getTransaction($transactionId): void
    {
        try {
            $response = $this->easyCreditHelper
                ->getTransactionApi()
                ->apiMerchantV3TransactionTransactionIdGet($transactionId);
            $this->sendJsonResponse($response);
        } catch (ApiException $apiException) {
            $this->sendJsonResponseFromException($apiException);
        } catch (\Throwable $throwable) {
            $this->sendJsonResponse(
                json_encode(
                    [
                        'error' => $throwable->getMessage(),
                    ],
                    500
                )
            );
        }
    }

    /**
     * @api
     */
    public function captureTransaction($transactionId): void
    {
        try {
            $bodyParams = $this->request->getBodyParams();
            ;

            $this->easyCreditHelper
                ->getTransactionApi()
                ->apiMerchantV3TransactionTransactionIdCapturePost(
                    $transactionId,
                    new CaptureRequest([
                        'trackingNumber' => $bodyParams['trackingNumber'] ?? null,
                    ])
                );
        } catch (ApiException $apiException) {
            $this->sendJsonResponseFromException($apiException);
        } catch (\Throwable $throwable) {
            $this->sendJsonResponse(
                json_encode(
                    [
                        'error' => $throwable->getMessage(),
                    ],
                    500
                )
            );
        }
    }

    /**
     * @api
     */
    public function refundTransaction($transactionId): void
    {
        try {
            $bodyParams = $this->request->getBodyParams();
            ;

            $this->easyCreditHelper
                ->getTransactionApi()
                ->apiMerchantV3TransactionTransactionIdRefundPost(
                    $transactionId,
                    new RefundRequest([
                        'value' => $bodyParams['value'],
                    ])
                );
        } catch (ApiException $apiException) {
            $this->sendJsonResponseFromException($apiException);
        } catch (\Throwable $throwable) {
            $this->sendJsonResponse(
                json_encode(
                    [
                        'error' => $throwable->getMessage(),
                    ],
                    500
                )
            );
        }
    }
}
