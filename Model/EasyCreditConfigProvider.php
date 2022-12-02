<?php
/*
 * (c) NETZKOLLEKTIV GmbH <kontakt@netzkollektiv.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Netzkollektiv\EasyCredit\Model;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\Escaper;
use Magento\Payment\Helper\Data as PaymentHelper;
use Magento\Framework\UrlInterface;
use Psr\Log\LoggerInterface;

use Netzkollektiv\EasyCredit\Helper\Data as EasyCreditHelper;

class EasyCreditConfigProvider implements ConfigProviderInterface
{
    /**
     * @var Escaper
     */
    private $escaper;

    /**
     * @var EasyCreditHelper
     */
    private $easyCreditHelper;

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @var LoggerInterface
     */
    private $logger;

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

    /**
     * {@inheritdoc}
     */
    public function getConfig()
    {
        $config['payment'][Payment::CODE] = '';
        try {
            $config['payment'][Payment::CODE] = [
                'apiKey'                => $this->escaper->escapeHtml($this->easyCreditHelper->getConfigValue('api_key')),
                'redirectUrl'           => $this->urlBuilder->getUrl('easycredit/checkout/start'),
                'defaultErrorMessage'   => implode(
                    ' ', [
                    'easyCredit-Ratenkauf ist derzeit nicht verfügbar.',
                    'Bitte versuchen Sie es später erneut.'
                    ]
                )
            ];
        } catch (\Exception $e) {
            $this->logger->critical($e);
        }
        return $config;
    }
}
