<?php
/*
 * (c) NETZKOLLEKTIV GmbH <kontakt@netzkollektiv.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Netzkollektiv\EasyCredit\Setup;

use Magento\Framework\Composer\ComposerFactory;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * @var ComposerFactory
     */
    private $composerFactory;

    /**
     * @param ComposerFactory $composerFactory
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __construct(ComposerFactory $composerFactory)
    {
        $this->composerFactory = $composerFactory;
    }

    public function getApiLibraryPackage()
    {
        $packages = $this->composerFactory->create()->getLocker()->getLockedRepository()->getPackages();
        foreach ($packages as $package) {
            if ($package->getName() == 'netzkollektiv/ratenkaufbyeasycredit-api-v3-php') {
                return $package;
            }
        }
    }

    /**
     * Upgrades DB schema
     *
     * @param  SchemaSetupInterface   $setup
     * @param  ModuleContextInterface $context
     * @return void
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $package = $this->getApiLibraryPackage();
        if (!$package) {
            throw new \Exception('Please run "composer require netzkollektiv/ratenkaufbyeasycredit-api-v3-php"');
        }

        if (!$context->getVersion()) {
            $setup->startSetup();
            $quoteAddressTable = 'quote_address';
            $orderTable = 'sales_order';
            $invoiceTable = 'sales_invoice';
            $creditmemoTable = 'sales_creditmemo';

            $setup->getConnection()
                ->addColumn(
                    $setup->getTable($quoteAddressTable),
                    'easycredit_amount',
                    [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                        'precision' => 10,
                        'scale' => 2,
                        'default' => 0.00,
                        'nullable' => true,
                        'comment' =>'easyCredit Amount'
                    ]
                );
            $setup->getConnection()
                ->addColumn(
                    $setup->getTable($quoteAddressTable),
                    'base_easycredit_amount',
                    [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                        'precision' => 10,
                        'scale' => 2,
                        'default' => 0.00,
                        'nullable' => true,
                        'comment' =>'Base easyCredit Amount'
                    ]
                );
            //Order tables
            $setup->getConnection()
                ->addColumn(
                    $setup->getTable($orderTable),
                    'easycredit_amount',
                    [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                        'precision' => 10,
                        'scale' => 2,
                        'default' => 0.00,
                        'nullable' => true,
                        'comment' =>'easyCredit Amount'
                    ]
                );
            $setup->getConnection()
                ->addColumn(
                    $setup->getTable($orderTable),
                    'base_easycredit_amount',
                    [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                        'precision' => 10,
                        'scale' => 2,
                        'default' => 0.00,
                        'nullable' => true,
                        'comment' =>'Base easyCredit Amount'
                    ]
                );
            $setup->getConnection()
                ->addColumn(
                    $setup->getTable($orderTable),
                    'easycredit_amount_refunded',
                    [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                        'precision' => 10,
                        'scale' => 2,
                        'default' => 0.00,
                        'nullable' => true,
                        'comment' =>'Base easyCredit Amount Refunded'
                    ]
                );
            $setup->getConnection()
                ->addColumn(
                    $setup->getTable($orderTable),
                    'base_easycredit_amount_refunded',
                    [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                        'precision' => 10,
                        'scale' => 2,
                        'default' => 0.00,
                        'nullable' => true,
                        'comment' =>'Base easyCredit Amount Refunded'
                    ]
                );
            $setup->getConnection()
                ->addColumn(
                    $setup->getTable($orderTable),
                    'easycredit_amount_invoiced',
                    [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                        'precision' => 10,
                        'scale' => 2,
                        'default' => 0.00,
                        'nullable' => true,
                        'comment' =>'easyCredit Amount Invoiced'
                    ]
                );
            $setup->getConnection()
                ->addColumn(
                    $setup->getTable($orderTable),
                    'base_easycredit_amount_invoiced',
                    [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                        'precision' => 10,
                        'scale' => 2,
                        'default' => 0.00,
                        'nullable' => true,
                        'comment' =>'Base easyCredit Amount Invoiced'
                    ]
                );
            //Invoice tables
            $setup->getConnection()
                ->addColumn(
                    $setup->getTable($invoiceTable),
                    'easycredit_amount',
                    [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                        'precision' => 10,
                        'scale' => 2,
                        'default' => 0.00,
                        'nullable' => true,
                        'comment' =>'Fee Amount'
                    ]
                );
            $setup->getConnection()
                ->addColumn(
                    $setup->getTable($invoiceTable),
                    'base_easycredit_amount',
                    [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                        'precision' => 10,
                        'scale' => 2,
                        'default' => 0.00,
                        'nullable' => true,
                        'comment' =>'Base Fee Amount'
                    ]
                );
            //Credit memo tables
            $setup->getConnection()
                ->addColumn(
                    $setup->getTable($creditmemoTable),
                    'easycredit_amount',
                    [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                        'precision' => 10,
                        'scale' => 2,
                        'default' => 0.00,
                        'nullable' => true,
                        'comment' =>'easyCredit Amount'
                    ]
                );
            $setup->getConnection()
                ->addColumn(
                    $setup->getTable($creditmemoTable),
                    'base_easycredit_amount',
                    [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                        'precision' => 10,
                        'scale' => 2,
                        'default' => 0.00,
                        'nullable' => true,
                        'comment' =>'Base easyCredit Amount'
                    ]
                );
            $setup->endSetup();
        }

        // version check for manual installations
        if (version_compare($context->getVersion(), '2.0.0') >= 0
            && version_compare($package->getVersion(), '1.3.4', '<')
        ) {
            throw new \Exception('Please upgrade ' . $package->getName() . ' to v1.3.4, run: "composer require ratenkaufbyeasycredit/php-sdk"');
        }
    }
}
