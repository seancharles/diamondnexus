<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_EditOrder
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\EditOrder\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Zend_Db_Exception;

/**
 * Class InstallSchema
 * @package Mageplaza\EditOrder\Setup
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     *
     * @throws Zend_Db_Exception
     * @SuppressWarnings(Unused)
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();
        $connection = $installer->getConnection();

        if (!$installer->tableExists('mageplaza_editorder_logs')) {
            $table = $connection
                ->newTable($installer->getTable('mageplaza_editorder_logs'))
                ->addColumn('log_id', Table::TYPE_INTEGER, null, [
                    'identity' => true,
                    'nullable' => false,
                    'primary'  => true,
                    'unsigned' => true
                ], 'Log ID')
                ->addColumn('order_id', Table::TYPE_INTEGER, null, [], 'Order Id')
                ->addColumn('editor_id', Table::TYPE_INTEGER, null, [], 'Admin Id')
                ->addColumn('editor', Table::TYPE_TEXT, 255, ['nullable => false'], 'Editor')
                ->addColumn('editor_ip', Table::TYPE_TEXT, 255, ['nullable => false'], 'Editor IP Address')
                ->addColumn('order_number', Table::TYPE_TEXT, 255, ['nullable => false'], 'Order Number')
                ->addColumn('edited_type', Table::TYPE_TEXT, '2M', ['nullable => false'], 'Edited Field')
                ->addColumn('old_data', Table::TYPE_TEXT, '2M', ['nullable => false'], 'Old Order Data')
                ->addColumn('new_data', Table::TYPE_TEXT, '2M', ['nullable => false'], 'New Order Data')
                ->addColumn('old_total_data', Table::TYPE_TEXT, '2M', ['nullable => false'], 'Old Total Data')
                ->addColumn(
                    'created_at',
                    Table::TYPE_TIMESTAMP,
                    null,
                    ['nullable' => false, 'default' => Table::TIMESTAMP_INIT],
                    'Creation Time'
                )
                ->setComment('Edit Order Manage Logs');
            $connection->createTable($table);
        }

        if ($installer->tableExists($installer->getTable('quote_item')) &&
            $installer->tableExists($installer->getTable('sales_order_item'))) {
            $columns = [
                'mp_custom_tax_percent'    => [
                    'type'    => Table::TYPE_TEXT,
                    'length'  => 255,
                    'comment' => 'Custom Tax Percent',
                ],
                'mp_custom_discount_type'  => [
                    'type'    => Table::TYPE_TEXT,
                    'length'  => 255,
                    'comment' => 'Custom Discount Type',
                ],
                'mp_custom_discount_value' => [
                    'type'    => Table::TYPE_TEXT,
                    'length'  => 255,
                    'comment' => 'Custom Discount Value',
                ]
            ];

            $quoteItemsTable = $installer->getTable($installer->getTable('quote_item'));
            $orderItemsTable = $installer->getTable($installer->getTable('sales_order_item'));
            foreach ($columns as $name => $definition) {
                $connection->addColumn($quoteItemsTable, $name, $definition);
                $connection->addColumn($orderItemsTable, $name, $definition);
            }
        }

        $installer->endSetup();
    }
}
