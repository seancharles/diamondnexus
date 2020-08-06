<?php

namespace ForeverCompanies\DynamicBundle\Setup;
 
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;
 
class InstallSchema implements InstallSchemaInterface
{
   public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
   {
       $setup->startSetup();
 
       $setup->getConnection()->addColumn(
           $setup->getTable('catalog_product_option_type_value'),
           'shippinggroup',
           [
               'type'     => Table::TYPE_TEXT,
               'nullable' => true,
               'default'  => null,
               'comment'  => 'Shipping Group Override',
           ]
       );
 
       $setup->endSetup();
   }
}