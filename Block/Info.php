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
        $this->setTemplate('Netzkollektiv_EasyCredit::easycredit/info/pdf/default.phtml');
        return $this->toHtml();
    }

    public function getPaymentPlan()
    {
        $summary = \json_decode((string) $this->getInfo()->getAdditionalInformation('summary'), null, 512, JSON_THROW_ON_ERROR);
        if ($summary === false) {
            return null;
        }

        if ($summary === null) {
            return null;
        }

        return json_encode($summary, JSON_THROW_ON_ERROR);
    }
}
