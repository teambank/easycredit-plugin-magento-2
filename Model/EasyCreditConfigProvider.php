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

class EasyCreditConfigProvider implements ConfigProviderInterface
{
    /**
     * @var \Magento\Payment\Model\Method\AbstractMethod[]
     */
    private $method;

    /**
     * @var Escaper
     */
    private $escaper;

    private $easyCreditCheckout;

    /**
     * @param PaymentHelper $paymentHelper
     * @param Escaper $escaper
     */
    public function __construct(
        PaymentHelper $paymentHelper,
        Escaper $escaper,
        \Magento\Framework\UrlInterface $urlBuilder,
        \Netzkollektiv\EasyCredit\Helper\Data $easyCreditHelper,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->method = $paymentHelper->getMethodInstance(Payment::CODE);
        $this->escaper = $escaper;
        $this->urlBuilder = $urlBuilder;
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
                'redirectUrl'           => $this->urlBuilder->getUrl('easycredit/checkout/start'),
                'defaultErrorMessage'   => implode(' ', [
                    'ratenkauf by easyCredit ist derzeit nicht verfügbar.',
                    'Bitte versuchen Sie es später erneut.'
                ])
            ];
        } catch (\Exception $e) {
            $this->logger->critical($e);
        }
        return $config;
    }
}
