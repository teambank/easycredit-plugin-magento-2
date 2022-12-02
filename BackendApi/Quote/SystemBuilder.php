<?php
/*
 * (c) NETZKOLLEKTIV GmbH <kontakt@netzkollektiv.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Netzkollektiv\EasyCredit\BackendApi\Quote;

class SystemBuilder
{
    private $productMetadata;
    private $moduleResource;

    public function __construct(
        \Magento\Framework\App\ProductMetadataInterface $productMetadata,
        \Magento\Framework\Module\ResourceInterface $moduleResource
    ) {
        $this->productMetadata = $productMetadata;
        $this->moduleResource = $moduleResource;
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
        return $this->moduleResource->getDbVersion('Netzkollektiv_EasyCredit');
    }

    public function build()
    {
        return new \Teambank\RatenkaufByEasyCreditApiV3\Model\Shopsystem(
            [
            'shopSystemManufacturer' => implode(' ', [$this->getSystemVendor(),$this->getSystemVersion()]),
            'shopSystemModuleVersion' => $this->getModuleVersion()
            ]
        );
    }
}
