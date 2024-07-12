<?php
/*
 * (c) NETZKOLLEKTIV GmbH <kontakt@netzkollektiv.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Netzkollektiv\EasyCredit\Model;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\Escaper;
use Magento\Framework\UrlInterface;
use Netzkollektiv\EasyCredit\Helper\Data as EasyCreditHelper;
use Psr\Log\LoggerInterface;
use Netzkollektiv\EasyCredit\Helper\Payment as PaymentHelper;

class EasyCreditConfigProvider implements ConfigProviderInterface
{
    private Escaper $escaper;

    private EasyCreditHelper $easyCreditHelper;

    private UrlInterface $urlBuilder;

    private LoggerInterface $logger;

    private PaymentHelper $paymentHelper;

    public function __construct(
        Escaper $escaper,
        UrlInterface $urlBuilder,
        EasyCreditHelper $easyCreditHelper,
        PaymentHelper $paymentHelper,
        LoggerInterface $logger
    ) {
        $this->escaper = $escaper;
        $this->urlBuilder = $urlBuilder;
        $this->easyCreditHelper = $easyCreditHelper;
        $this->paymentHelper = $paymentHelper;
        $this->logger = $logger;
    }

    public function getConfig()
    {
        $config = [];
        $config['payment']['easycredit']['apiKey'] = $this->escaper->escapeHtml($this->easyCreditHelper->getConfigValue('credentials/api_key'));
        foreach ($this->paymentHelper->getAvailableMethods() as $method) {
            $config['payment'][$method::CODE] = '';
            try {
                $config['payment'][$method::CODE] = [
                    'paymentType' => $this->paymentHelper->getTypeByMethod($method::CODE),
                    'redirectUrl' => $this->urlBuilder->getUrl('easycredit/checkout/start'),
                    'defaultErrorMessage' => implode(
                        ' ',
                        [
                            'easyCredit ist derzeit nicht verfügbar.',
                            'Bitte versuchen Sie es später erneut.',
                        ]
                    ),
                ];
            } catch (\Exception $exception) {
                $this->logger->critical($exception);
            }
        }

        return $config;
    }
}
