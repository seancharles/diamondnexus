<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace ForeverCompanies\Salesforce\Setup;

use Magento\Framework\Setup\SetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;

/**
 * Class UpgradeSchema
 * @package ForeverCompanies\Salesforce\Setup
 */
class UpgradeSchema implements UpgradeSchemaInterface
{

    /**
     * Upgrade database when run bin/magento setup:upgrade from command line
     *
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @throws \Zend_Db_Exception
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();

        if (version_compare($context->getVersion(), '1.0.1') < 0) {
            $this->createRequestTable($installer);
        }

        $installer->endSetup();
    }

    /**
     * Create the table forevercompanies_salesforce_request
     *
     * @param SetupInterface $installer
     * @return void
     */
    private function createRequestTable($installer)
    {
        $tableName = 'forevercompanies_salesforce_request';
        if ($installer->tableExists($tableName)) {
            return;
        }

        $table = $installer->getConnection()->newTable(
            $installer->getTable($tableName)
        )->addColumn(
            'id',
            Table::TYPE_INTEGER,
            null,
            [
                'identity' => true,
                'unsigned' => true,
                'nullable' => false,
                'primary' => true
            ],
            'Id'
        )->addColumn(
            'date',
            Table::TYPE_DATE,
            null,
            ['nullable' => false],
            'Date'
        )->addColumn(
            'rest_request',
            Table::TYPE_INTEGER,
            null,
            ['nullable' => true],
            'Request'
        )->addColumn(
            'bulk_request',
            Table::TYPE_INTEGER,
            null,
            ['nullable' => true],
            'Request'
        )->setComment(
            'Salesforce Request Table'
        );

        $installer->getConnection()->createTable($table);
    }
}