<?php
/*
 * (c) NETZKOLLEKTIV GmbH <kontakt@netzkollektiv.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Netzkollektiv\EasyCredit\BackendApi\Quote;

class System implements \Netzkollektiv\EasyCreditApi\SystemInterface
{
    public function __construct(
        \Magento\Framework\App\ProductMetadataInterface $productMetadata,
        \Magento\Framework\Module\ResourceInterface $moduleResource
    ) {
        $this->productMetadata = $productMetadata;
        $this->_moduleResource = $moduleResource;
    }

    public function getSystemVendor()
    {
        return 'Magento';
    }

    public function getSystemVersion()
    {
        return $this->productMetadata->getVersion();
    }

    public function getModuleVersion()
    {
        return $this->_moduleResource->getDbVersion('Netzkollektiv_EasyCredit');
    }

    public function getIntegration()
    {
        return 'PAYMENT_PAGE';
    }
}
