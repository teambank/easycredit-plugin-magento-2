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

class EasyCreditConfigProvider implements ConfigProviderInterface
{
    private Escaper $escaper;

    private EasyCreditHelper $easyCreditHelper;

    private UrlInterface $urlBuilder;

    private LoggerInterface $logger;

    public function __construct(
        Escaper $escaper,
        UrlInterface $urlBuilder,
        EasyCreditHelper $easyCreditHelper,
        LoggerInterface $logger
    ) {
        $this->escaper = $escaper;
        $this->urlBuilder = $urlBuilder;
        $this->easyCreditHelper = $easyCreditHelper;
        $this->logger = $logger;
    }

    public function getConfig()
    {
        $config = [];
        $config['payment'][Payment::CODE] = '';
        try {
            $config['payment'][Payment::CODE] = [
                'apiKey' => $this->escaper->escapeHtml($this->easyCreditHelper->getConfigValue('credentials/api_key')),
                'redirectUrl' => $this->urlBuilder->getUrl('easycredit/checkout/start'),
                'defaultErrorMessage' => implode(
                    ' ',
                    [
                        'easyCredit-Ratenkauf ist derzeit nicht verfügbar.',
                        'Bitte versuchen Sie es später erneut.',
                    ]
                ),
            ];
        } catch (\Exception $exception) {
            $this->logger->critical($exception);
        }

        return $config;
    }
}
