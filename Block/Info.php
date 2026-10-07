<?php
/*
 * (c) NETZKOLLEKTIV GmbH <kontakt@netzkollektiv.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Netzkollektiv\EasyCredit\Block;

use Magento\Payment\Block\Info as PaymentInfo;

class Info extends PaymentInfo
{
    protected $_template = 'Netzkollektiv_EasyCredit::easycredit/info.phtml';

    /**
     * Render as PDF
     *
     * @return string
     */
    public function toPdf()
    {
        $previousTemplate = $this->_template;
        $this->_template = 'Netzkollektiv_EasyCredit::easycredit/info/pdf/default.phtml';
        $html = $this->toHtml();
        $this->_template = $previousTemplate;

        return $html;
    }

    public function getPaymentPlan()
    {
        return $this->encodePaymentPlan(
            $this->getInfo()->getAdditionalInformation('summary')
        );
    }

    /**
     * @param mixed $summary
     */
    private function encodePaymentPlan($summary): ?string
    {
        if (! is_string($summary) || $summary === '') {
            return null;
        }

        try {
            $decoded = json_decode($summary, null, 512, JSON_THROW_ON_ERROR);
            if ($decoded === null) {
                return null;
            }

            return json_encode($decoded, JSON_THROW_ON_ERROR);
        } catch (\JsonException $jsonException) {
            return null;
        }
    }
}
