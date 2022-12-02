<?php
/*
 * (c) NETZKOLLEKTIV GmbH <kontakt@netzkollektiv.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Netzkollektiv\EasyCredit\Model;

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

class Payment extends \Magento\Payment\Model\Method\AbstractMethod
{
    const CODE = 'easycredit';

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

    /**
     * @var \Netzkollektiv\EasyCredit\Helper\Data
     */
    protected $easyCreditHelper;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var ManagerInterface
     */
    private $eventDispatcher;

    protected $timezone;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory,
        \Magento\Payment\Helper\Data $paymentData,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Payment\Model\Method\Logger $logger,
        \Magento\Framework\UrlInterface $urlBuilder,
        \Netzkollektiv\EasyCredit\Helper\Data $easyCreditHelper,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->easyCreditHelper = $easyCreditHelper;

        $this->urlBuilder = $urlBuilder;
        $this->scopeConfig = $scopeConfig;
        $this->timezone = $timezone;
        $this->eventDispatcher = $context->getEventDispatcher();

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
        if ($quote === null
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
     * @throws                                        \Magento\Framework\Exception\LocalizedException
     * @api
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @deprecated                                    100.2.0
     */
    public function refund(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        if (!$this->canRefund()) {
            throw new \Magento\Framework\Exception\LocalizedException(__('The refund action is not available.'));
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
        } catch (\Exception $e) {
            throw new LocalizedException(__($e->getMessage()));
        }
        return $this;
    }

    /**
     * {inheritdoc}
     *
     * @param  DataObject $data
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
     * @throws                                        \Magento\Framework\Exception\LocalizedException
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

        } catch (\Exception $e) {
            throw new LocalizedException(__($e->getMessage()));
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
     * @throws                                        \Magento\Framework\Exception\LocalizedException
     * @api
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @deprecated                                    100.2.0
     */
    public function order(InfoInterface $payment, $amount)
    {
        if (!$this->canOrder()) {
            throw new \Magento\Framework\Exception\LocalizedException(__('The authorize action is not available.'));
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
        } catch (\Exception $e) {
            throw new LocalizedException(__($e->getMessage()));
        }
        return $this;
    }
}
