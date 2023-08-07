<?php
/*
 * (c) NETZKOLLEKTIV GmbH <kontakt@netzkollektiv.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Netzkollektiv\EasyCredit\Model;

use Magento\Payment\Model\Method\AbstractMethod;
use Netzkollektiv\EasyCredit\Helper\Data;
use Magento\Framework\UrlInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Framework\Api\AttributeValueFactory;
use Magento\Payment\Model\Method\Logger;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Model\InfoInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Sales\Model\Order;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\DataObject;
use Magento\Quote\Api\Data\PaymentInterface;

use Teambank\RatenkaufByEasyCreditApiV3\Model\CaptureRequest;
use Teambank\RatenkaufByEasyCreditApiV3\Model\RefundRequest;
use Teambank\RatenkaufByEasyCreditApiV3\ApiException;

use Netzkollektiv\EasyCredit\Block\Info;
use Netzkollektiv\EasyCredit\Exception\TransactionNotFoundException;

class Payment extends AbstractMethod
{
    public const CODE = 'easycredit';

    /**
     * Payment method code
     *
     * @var string
     */
    protected $_code = self::CODE;

    /**
     * Cash On Delivery payment block paths
     *
     * @var string
     */
    protected $_infoBlockType = Info::class;

    /**
     * Payment Method feature
     *
     * @var bool
     */
    protected $_canOrder = true;

    /**
     * Payment Method feature
     *
     * @var bool
     */
    protected $_canAuthorize = true;

    /**
     * Payment Method feature
     *
     * @var bool
     */
    protected $_canCapture = true;

    /**
     * Payment Method feature
     *
     * @var bool
     */
    protected $_canRefund = true;

    /**
     * Payment Method feature
     *
     * @var bool
     */
    protected $_canRefundInvoicePartial = true;

    protected $_supportedCurrencyCodes = ['EUR'];

    /**
     * Payment Method feature
     *
     * @var bool
     */
    protected $_isGateway = true;

    protected Data $easyCreditHelper;

    protected UrlInterface $urlBuilder;

    protected ScopeConfigInterface $scopeConfig;

    protected TimezoneInterface $timezone;

    public function __construct(
        Context $context,
        Registry $registry,
        ExtensionAttributesFactory $extensionFactory,
        AttributeValueFactory $customAttributeFactory,
        \Magento\Payment\Helper\Data $paymentData,
        ScopeConfigInterface $scopeConfig,
        Logger $logger,
        UrlInterface $urlBuilder,
        Data $easyCreditHelper,
        TimezoneInterface $timezone,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->easyCreditHelper = $easyCreditHelper;

        $this->urlBuilder = $urlBuilder;
        $this->scopeConfig = $scopeConfig;
        $this->timezone = $timezone;

        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $paymentData,
            $scopeConfig,
            $logger,
            $resource,
            $resourceCollection,
            $data
        );
    }

    public function isAvailable(CartInterface $quote = null)
    {
        if (!$quote instanceof CartInterface
            || !$this->getConfigData('credentials/api_key')
            || !$this->getConfigData('credentials/api_token')
        ) {
            return false;
        }

        return parent::isAvailable($quote);
    }

    /**
     * Get config payment action url
     * Used to universalize payment actions when processing payment place
     *
     * @return     string
     * @api
     * @deprecated 100.2.0
     */
    public function getConfigPaymentAction()
    {
        return self::ACTION_ORDER;
    }

    /**
     * Refund specified amount for payment
     *
     * @param                                         DataObject|InfoInterface $payment
     * @param                                         float                    $amount
     * @return                                        $this
     * @throws LocalizedException
     * @api
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @deprecated                                    100.2.0
     */
    public function refund(InfoInterface $payment, $amount)
    {
        if (!$this->canRefund()) {
            throw new LocalizedException(__('The refund action is not available.'));
        }

        try {
            $txId = $payment->getAdditionalInformation('transaction_id');

            $this->_getTransaction($txId);

            $this->easyCreditHelper
                ->getTransactionApi()
                ->apiMerchantV3TransactionTransactionIdRefundPost(
                    $txId,
                    new RefundRequest(['value' => $amount])
                );
        } catch (\Exception $exception) {
            throw new LocalizedException(__($exception->getMessage()));
        }

        return $this;
    }

    /**
     * {inheritdoc}
     *
     * @return $this
     */
    public function assignData(DataObject $data)
    {
        $additionalData = $data->getData(PaymentInterface::KEY_ADDITIONAL_DATA);
        if (!is_object($additionalData)) {
            $additionalData = new DataObject($additionalData ?: []);
        }

        $this->getInfoInstance()->setAdditionalInformation('duration', $additionalData->getDuration());

        return parent::assignData($data);
    }

    /**
     * Capture payment abstract method
     *
     * @param                                         DataObject|InfoInterface $payment
     * @param                                         float                    $amount
     * @return                                        $this
     * @throws LocalizedException
     * @api
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @deprecated                                    100.2.0
     */
    public function capture(InfoInterface $payment, $amount)
    {
        if (!$this->canCapture()) {
            throw new LocalizedException(__('Capture action is not available.'));
        }

        try {
            $txId = $payment->getAdditionalInformation('transaction_id');

            $this->_getTransaction($txId);

            $this->easyCreditHelper
                ->getTransactionApi()
                ->apiMerchantV3TransactionTransactionIdCapturePost(
                    $txId,
                    new CaptureRequest([])
                );

        } catch (\Exception $exception) {
            throw new LocalizedException(__($exception->getMessage()));
        }

        return $this;
    }

    protected function _getTransaction($txId)
    {
        try { 
            $transaction = $this->easyCreditHelper
                ->getTransactionApi()
                ->apiMerchantV3TransactionTransactionIdGet($txId);
        } catch (ApiException $e) {
            throw new TransactionNotFoundException(
                'Payment transaction not found. 
                It can take up to 24 hours until the transaction is available in the merchant portal. 
                If you still want to create the invoice immediately, please use "Capture Offline".'
            );
        } catch (\Exception $e) {
            throw new TransactionNotFoundException(
                'An error occured when searching the transaction.'
            );
        }

        return $transaction;
    }

    /**
     * Authorize payment abstract method
     *
     * @param                                         DataObject|InfoInterface $payment
     * @param                                         float                    $amount
     * @return                                        $this
     * @throws LocalizedException
     * @api
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @deprecated                                    100.2.0
     */
    public function order(InfoInterface $payment, $amount)
    {
        if (!$this->canOrder()) {
            throw new LocalizedException(__('The authorize action is not available.'));
        }

        try {
            if (!$this->easyCreditHelper->getCheckout()->authorize($payment->getOrder()->getIncrementId())
            ) {
                throw new \Exception('Transaction could not be authorized');
            }

            $payment->setTransactionId(
                $payment->getAdditionalInformation('transaction_id')
            )->setIsTransactionClosed(false)
                ->setIsTransactionPending(true);
        } catch (\Exception $exception) {
            throw new LocalizedException(__($exception->getMessage()));
        }

        return $this;
    }
}
