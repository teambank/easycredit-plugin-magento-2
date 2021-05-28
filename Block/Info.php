<?php
/*
 * (c) NETZKOLLEKTIV GmbH <kontakt@netzkollektiv.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Netzkollektiv\EasyCredit\Block;

use Magento\Payment\Block\ConfigurableInfo;

class Info extends ConfigurableInfo
{
    protected function _prepareSpecificInformation($transport = null)
    {
        $transport = parent::_prepareSpecificInformation($transport);
        $payment = $this->getInfo();

        foreach (['transaction_id'] as $field) {
            $fieldData = $payment->getAdditionalInformation($field);

            if ($fieldData !== null && !empty($fieldData)) {
                $this->setDataToTransfer(
                    $transport,
                    $field,
                    $fieldData
                );
            }
        }

        return $transport;
    }

    protected function getLabel($field)
    {
        return __($field);
    }
}
