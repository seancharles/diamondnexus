<?php

namespace ForeverCompanies\LooseStoneImport\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{
    
    /**
     * {@inheritdoc}
     */
    public function upgrade(
        SchemaSetupInterface $setup,
        ModuleContextInterface $context
        ) {
            $installer = $setup;
            
            $installer->startSetup();
           
      
        if (!$installer->tableExists('stone_log')) {
            $table = $installer->getConnection()
            ->newTable($installer->getTable('stone_log'))
            ->addColumn(
                'id',
                \Magento\Framework\DB\Ddl\Table::TYPE_BIGINT,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' =>
                    false, 'primary' => true],
                'Stone ID'
            )
            ->addColumn(
                'sku',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'SKU'
            )
            ->addColumn(
                'log_action',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                20,
                ['nullable' => false],
                'Log Action'
            )
            ->addColumn(
                'log_date',
                \Magento\Framework\DB\Ddl\Table::TYPE_DATE,
                null,
                ['nullable' => false,],
                'Log Date'
            )
            ->addColumn(
                'payload',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                '16M',
                ['nullable' => false],
                'Payload'
            )
            ->addColumn(
                'payload_hash',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'Payload Hash'
            )
            ->addColumn(
                'errors',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                '16M',
                ['nullable' => false],
                'Error Log'
            );
            $installer->getConnection()->createTable($table);
        }
        $installer->endSetup();
    }
}
