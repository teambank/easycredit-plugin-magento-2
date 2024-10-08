<?php
/*
 * (c) NETZKOLLEKTIV GmbH <kontakt@netzkollektiv.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Netzkollektiv\EasyCredit\BackendApi\Quote;

use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\Module\ResourceInterface;
use Teambank\EasyCreditApiV3\Model\Shopsystem;

class SystemBuilder
{
    private ProductMetadataInterface $productMetadata;

    private ResourceInterface $moduleResource;

    public function __construct(
        ProductMetadataInterface $productMetadata,
        ResourceInterface $moduleResource
    ) {
        $this->productMetadata = $productMetadata;
        $this->moduleResource = $moduleResource;
    }

    public function getSystemVendor(): string
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

    public function build(): Shopsystem
    {
        return new Shopsystem(
            [
                'shopSystemManufacturer' => implode(' ', [$this->getSystemVendor(), $this->getSystemVersion()]),
                'shopSystemModuleVersion' => $this->getModuleVersion(),
            ]
        );
    }
}
