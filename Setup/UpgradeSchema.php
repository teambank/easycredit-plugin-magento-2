<?php
/*
 * (c) NETZKOLLEKTIV GmbH <kontakt@netzkollektiv.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Netzkollektiv\EasyCredit\Setup;

use Composer\Package\CompletePackageInterface;
use Composer\Semver\Comparator;
use Magento\Framework\Composer\ComposerFactory;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{
    private ComposerFactory $composerFactory;

    /**
     * @var string
     */
    private const QUOTE_ADDRESS_TABLE = 'quote_address';

    /**
     * @var string
     */
    private const ORDER_TABLE = 'sales_order';

    /**
     * @var string
     */
    private const INVOICE_TABLE = 'sales_invoice';

    /**
     * @var string
     */
    private const CREDITMEMO_TABLE = 'sales_creditmemo';

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __construct(ComposerFactory $composerFactory)
    {
        $this->composerFactory = $composerFactory;
    }

    public function getApiLibraryPackage(): ?CompletePackageInterface
    {
        $packages = $this->composerFactory->create()->getLocker()->getLockedRepository()->getPackages();
        /** @var CompletePackageInterface $package */
        foreach ($packages as $package) {
            if ($package instanceof CompletePackageInterface && $package->getName() == 'netzkollektiv/ratenkaufbyeasycredit-api-v3-php') {
                return $package;
            }
        }

        return null;
    }

    /**
     * Upgrades DB schema
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $package = $this->getApiLibraryPackage();
        if (! $package instanceof CompletePackageInterface) {
            throw new \Exception('Please run "composer require netzkollektiv/ratenkaufbyeasycredit-api-v3-php:' . $package->getVersion());
        }

        $composer = json_decode(file_get_contents(__DIR__ . '/../composer.json'), null, 512, JSON_THROW_ON_ERROR);

        if (Comparator::lessThan($package->getVersion(), $composer->require->{'netzkollektiv/ratenkaufbyeasycredit-api-v3-php'})) {
            throw new \Exception('Please upgrade ' . $package->getName() . ' to v' . $composer->require->{'netzkollektiv/ratenkaufbyeasycredit-api-v3-php'} . ', run: "composer require netzkollektiv/ratenkaufbyeasycredit-api-v3-php:' . $composer->require->{'netzkollektiv/ratenkaufbyeasycredit-api-v3-php'} . '"');
        }

        if (! $context->getVersion()) {
            $setup->startSetup();

            $setup->getConnection()
                ->addColumn(
                    $setup->getTable(self::QUOTE_ADDRESS_TABLE),
                    'easycredit_amount',
                    [
                        'type' => Table::TYPE_DECIMAL,
                        'precision' => 10,
                        'scale' => 2,
                        'default' => 0.00,
                        'nullable' => true,
                        'comment' => 'easyCredit Amount',
                    ]
                );
            $setup->getConnection()
                ->addColumn(
                    $setup->getTable(self::QUOTE_ADDRESS_TABLE),
                    'base_easycredit_amount',
                    [
                        'type' => Table::TYPE_DECIMAL,
                        'precision' => 10,
                        'scale' => 2,
                        'default' => 0.00,
                        'nullable' => true,
                        'comment' => 'Base easyCredit Amount',
                    ]
                );
            //Order tables
            $setup->getConnection()
                ->addColumn(
                    $setup->getTable(self::ORDER_TABLE),
                    'easycredit_amount',
                    [
                        'type' => Table::TYPE_DECIMAL,
                        'precision' => 10,
                        'scale' => 2,
                        'default' => 0.00,
                        'nullable' => true,
                        'comment' => 'easyCredit Amount',
                    ]
                );
            $setup->getConnection()
                ->addColumn(
                    $setup->getTable(self::ORDER_TABLE),
                    'base_easycredit_amount',
                    [
                        'type' => Table::TYPE_DECIMAL,
                        'precision' => 10,
                        'scale' => 2,
                        'default' => 0.00,
                        'nullable' => true,
                        'comment' => 'Base easyCredit Amount',
                    ]
                );
            $setup->getConnection()
                ->addColumn(
                    $setup->getTable(self::ORDER_TABLE),
                    'easycredit_amount_refunded',
                    [
                        'type' => Table::TYPE_DECIMAL,
                        'precision' => 10,
                        'scale' => 2,
                        'default' => 0.00,
                        'nullable' => true,
                        'comment' => 'Base easyCredit Amount Refunded',
                    ]
                );
            $setup->getConnection()
                ->addColumn(
                    $setup->getTable(self::ORDER_TABLE),
                    'base_easycredit_amount_refunded',
                    [
                        'type' => Table::TYPE_DECIMAL,
                        'precision' => 10,
                        'scale' => 2,
                        'default' => 0.00,
                        'nullable' => true,
                        'comment' => 'Base easyCredit Amount Refunded',
                    ]
                );
            $setup->getConnection()
                ->addColumn(
                    $setup->getTable(self::ORDER_TABLE),
                    'easycredit_amount_invoiced',
                    [
                        'type' => Table::TYPE_DECIMAL,
                        'precision' => 10,
                        'scale' => 2,
                        'default' => 0.00,
                        'nullable' => true,
                        'comment' => 'easyCredit Amount Invoiced',
                    ]
                );
            $setup->getConnection()
                ->addColumn(
                    $setup->getTable(self::ORDER_TABLE),
                    'base_easycredit_amount_invoiced',
                    [
                        'type' => Table::TYPE_DECIMAL,
                        'precision' => 10,
                        'scale' => 2,
                        'default' => 0.00,
                        'nullable' => true,
                        'comment' => 'Base easyCredit Amount Invoiced',
                    ]
                );
            //Invoice tables
            $setup->getConnection()
                ->addColumn(
                    $setup->getTable(self::INVOICE_TABLE),
                    'easycredit_amount',
                    [
                        'type' => Table::TYPE_DECIMAL,
                        'precision' => 10,
                        'scale' => 2,
                        'default' => 0.00,
                        'nullable' => true,
                        'comment' => 'Fee Amount',
                    ]
                );
            $setup->getConnection()
                ->addColumn(
                    $setup->getTable(self::INVOICE_TABLE),
                    'base_easycredit_amount',
                    [
                        'type' => Table::TYPE_DECIMAL,
                        'precision' => 10,
                        'scale' => 2,
                        'default' => 0.00,
                        'nullable' => true,
                        'comment' => 'Base Fee Amount',
                    ]
                );
            //Credit memo tables
            $setup->getConnection()
                ->addColumn(
                    $setup->getTable(self::CREDITMEMO_TABLE),
                    'easycredit_amount',
                    [
                        'type' => Table::TYPE_DECIMAL,
                        'precision' => 10,
                        'scale' => 2,
                        'default' => 0.00,
                        'nullable' => true,
                        'comment' => 'easyCredit Amount',
                    ]
                );
            $setup->getConnection()
                ->addColumn(
                    $setup->getTable(self::CREDITMEMO_TABLE),
                    'base_easycredit_amount',
                    [
                        'type' => Table::TYPE_DECIMAL,
                        'precision' => 10,
                        'scale' => 2,
                        'default' => 0.00,
                        'nullable' => true,
                        'comment' => 'Base easyCredit Amount',
                    ]
                );
            $setup->endSetup();
        }
    }
}
