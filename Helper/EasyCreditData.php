<?php
/*
 * (c) NETZKOLLEKTIV GmbH <kontakt@netzkollektiv.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Netzkollektiv\EasyCredit\Helper;

use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\App\Cache\Type\Config;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;
use Netzkollektiv\EasyCredit\BackendApi\StorageFactory;
use Netzkollektiv\EasyCredit\Logger\Logger;
use Teambank\EasyCreditApiV3 as Api;
use Teambank\EasyCreditApiV3\Client;
use Teambank\EasyCreditApiV3\Integration\CheckoutFactory;
use Teambank\EasyCreditApiV3\Integration\Util\AddressValidator;
use Teambank\EasyCreditApiV3\Integration\Util\PrefixConverter;
use Teambank\EasyCreditApiV3\Service\InstallmentplanApiFactory;
use Teambank\EasyCreditApiV3\Service\TransactionApiFactory;
use Teambank\EasyCreditApiV3\Service\WebshopApiFactory;

class EasyCreditData extends AbstractHelper
{
    private const CACHE_KEY = 'easycredit_webshop_info';

    private const CACHE_LIFETIME = 3600; // 1 hour in seconds

    /**
     * @var CheckoutSession
     */
    private $checkoutSession;

    /**
     * @var StorageFactory
     */
    private $storageFactory;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var CacheInterface
     */
    private $cache;

    /**
     * @var TransactionApiFactory
     */
    private $transactionApiFactory;

    /**
     * @var WebshopApiFactory
     */
    private $webshopApiFactory;

    /**
     * @var InstallmentplanApiFactory
     */
    private $installmentplanApiFactory;

    /**
     * @var CheckoutFactory
     */
    private $checkoutFactory;

    /**
     * @var AddressValidator
     */
    private $addressValidator;

    /**
     * @var PrefixConverter
     */
    private $prefixConverter;

    public function __construct(
        Context $context,
        CheckoutSession $checkoutSession,
        StorageFactory $storageFactory,
        Logger $logger,
        CacheInterface $cache,
        TransactionApiFactory $transactionApiFactory,
        WebshopApiFactory $webshopApiFactory,
        InstallmentplanApiFactory $installmentplanApiFactory,
        CheckoutFactory $checkoutFactory,
        AddressValidator $addressValidator,
        PrefixConverter $prefixConverter
    ) {
        parent::__construct($context);

        $this->checkoutSession = $checkoutSession;
        $this->storageFactory = $storageFactory;
        $this->logger = $logger;
        $this->cache = $cache;
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
            ->getValue('payment/easycredit/' . $key, ScopeInterface::SCOPE_STORE);
    }

    private function getConfig()
    {
        return Api\Configuration::getDefaultConfiguration()
            ->setHost('https://ratenkauf.easycredit.de')
            ->setUsername($this->getConfigValue('credentials/api_key'))
            ->setPassword($this->getConfigValue('credentials/api_token'))
            ->setAccessToken($this->getConfigValue('credentials/api_signature'));
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
            'config' => $this->getConfig(),
        ];

        $webshopApi = $this->webshopApiFactory->create($args);
        $transactionApi = $this->transactionApiFactory->create($args);
        $installmentplanApi = $this->installmentplanApiFactory->create($args);

        $storage = $this->storageFactory->create(
            [
                'payment' => ($quote) ? $quote->getPayment() : $this->checkoutSession->getQuote()->getPayment(),
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
                'logger' => $this->logger,
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
                'config' => $config,
            ]
        );
    }

    public function getWebshopDetails()
    {
        $cacheKey = self::CACHE_KEY;
        $cachedData = $this->cache->load($cacheKey);

        if ($cachedData !== false) {
            return json_decode($cachedData, true);
        }

        try {
            $result = $this->getCheckout()->getWebshopDetails();

            if ($result !== null) {
                $this->cache->save(
                    json_encode($result),
                    $cacheKey,
                    [Config::CACHE_TAG],
                    self::CACHE_LIFETIME
                );
            }

            return $result;
        } catch (\Exception $e) {
            return null;
        }
    }
}
