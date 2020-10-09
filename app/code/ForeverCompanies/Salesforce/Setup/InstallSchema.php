<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace ForeverCompanies\Salesforce\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;

/**
 * @codeCoverageIgnore
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * {@inheritdoc}
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();
      ;
        $table = $installer->getConnection()->newTable(
            $installer->getTable('forevercompanies_safesforce_map')
        )->addColumn(
            'id',
            Table::TYPE_INTEGER,
            null,
            [
                'identity' => true,
                'nullable' => false,
                'primary' => true,
            ],
            'Map ID'
        )->addColumn(
            'salesforce',
            Table::TYPE_TEXT,
            50,
            ['nullable' => false],
            'Salesforce Field'
        )->addColumn(
            'magento',
            Table::TYPE_TEXT,
            50,
            ['nullable' => false],
            'Magento Field'
        )->addColumn(
            'type',
            Table::TYPE_TEXT,
            null,
            [],
            'Type'
        )->addColumn(
            'description',
            Table::TYPE_TEXT,
            '2M',
            ['nullable' => false],
            'Description'
        )->addColumn(
            'status',
            Table::TYPE_SMALLINT,
            null,
            [],
            'Active Status'
        )->setComment(
            'Mapping Table'
        );
        $installer->getConnection()->createTable($table);
        $installer->endSetup();
    }

}
