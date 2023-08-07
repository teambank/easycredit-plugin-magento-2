<?php
/*
 * (c) NETZKOLLEKTIV GmbH <kontakt@netzkollektiv.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Netzkollektiv\EasyCredit\Helper;

use Teambank\RatenkaufByEasyCreditApiV3\Service\TransactionApiFactory;
use Teambank\RatenkaufByEasyCreditApiV3\Service\WebshopApiFactory;
use Teambank\RatenkaufByEasyCreditApiV3\Service\InstallmentplanApiFactory;
use Teambank\RatenkaufByEasyCreditApiV3\Integration\CheckoutFactory;
use Teambank\RatenkaufByEasyCreditApiV3\Integration\Util\AddressValidator;
use Teambank\RatenkaufByEasyCreditApiV3\Integration\Util\PrefixConverter;
use Teambank\RatenkaufByEasyCreditApiV3\Client;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;
use Netzkollektiv\EasyCredit\BackendApi\StorageFactory;
use Netzkollektiv\EasyCredit\Logger\Logger;
use Teambank\RatenkaufByEasyCreditApiV3 as Api;

class Data extends AbstractHelper
{
    private CheckoutSession $checkoutSession;

    private StorageFactory $storageFactory;

    private Logger $logger;

    /**
     * @var Api\Service\TransactionApiFactory
     */
    private TransactionApiFactory $transactionApiFactory;

    /**
     * @var Api\Service\WebshopApiFactory
     */
    private WebshopApiFactory $webshopApiFactory;

    /**
     * @var Api\Service\InstallmentplanApiFactory
     */
    private InstallmentplanApiFactory $installmentplanApiFactory;

    /**
     * @var Api\Integration\CheckoutFactory
     */
    private CheckoutFactory $checkoutFactory;

    /**
     * @var Api\Integration\Util\AddressValidator
     */
    private AddressValidator $addressValidator;

    /**
     * @var Api\Integration\Util\PrefixConverter
     */
    private PrefixConverter $prefixConverter;

    public function __construct(
        Context $context,
        CheckoutSession $checkoutSession,
        ScopeConfigInterface $scopeConfig,
        StorageFactory $storageFactory,
        Logger $logger,
        Api\Service\TransactionApiFactory $transactionApiFactory,
        Api\Service\WebshopApiFactory $webshopApiFactory,
        Api\Service\InstallmentplanApiFactory $installmentplanApiFactory,
        Api\Integration\CheckoutFactory $checkoutFactory,
        Api\Integration\Util\AddressValidator $addressValidator,
        Api\Integration\Util\PrefixConverter $prefixConverter
    ) {
        parent::__construct(
            $context
        );

        $this->checkoutSession = $checkoutSession;
        $this->scopeConfig = $scopeConfig;
        $this->storageFactory = $storageFactory;
        $this->logger = $logger;

        $this->transactionApiFactory = $transactionApiFactory;
        $this->webshopApiFactory = $webshopApiFactory;
        $this->installmentplanApiFactory = $installmentplanApiFactory;

        $this->checkoutFactory = $checkoutFactory;
        $this->addressValidator = $addressValidator;
        $this->prefixConverter = $prefixConverter;
    }

    public function getConfigValue(string $key)
    {
        return $this->scopeConfig
            ->getValue('payment/easycredit/credentials/' . $key, ScopeInterface::SCOPE_STORE);
    }

    private function getConfig()
    {
        return Api\Configuration::getDefaultConfiguration()
            ->setHost('https://ratenkauf.easycredit.de')
            ->setUsername($this->getConfigValue('api_key'))
            ->setPassword($this->getConfigValue('api_token'))
            ->setAccessToken($this->getConfigValue('api_signature'));
    }

    private function getClient(): Client
    {
        return new Client(
            $this->logger
        );
    }

    public function getCheckout($quote = null)
    {
            $args = [
                'client' => $this->getClient(),
                'config' => $this->getConfig()
            ];

            $webshopApi = $this->webshopApiFactory->create($args);
            $transactionApi = $this->transactionApiFactory->create($args);
            $installmentplanApi = $this->installmentplanApiFactory->create($args);

            $storage = $this->storageFactory->create(
                [
                'payment' => ($quote) ? $quote->getPayment() : $this->checkoutSession->getQuote()->getPayment()
                ]
            );

            return $this->checkoutFactory->create(
                [
                'webshopApi' => $webshopApi,
                'transactionApi' => $transactionApi,
                'installmentplanApi' => $installmentplanApi,
                'storage' => $storage,
                'addressValidator' => $this->addressValidator,
                'prefixConverter' => $this->prefixConverter,
                'logger' => $this->logger
                ]
            );
    }

    public function getTransactionApi(): Api\Service\TransactionApi
    {
        $client = $this->getClient();
        $config = clone $this->getConfig();
        $config->setHost('https://partner.easycredit-ratenkauf.de');

        return $this->transactionApiFactory->create(
            [
            'client' => $client,
            'config' => $config
            ]
        );
    }
}
