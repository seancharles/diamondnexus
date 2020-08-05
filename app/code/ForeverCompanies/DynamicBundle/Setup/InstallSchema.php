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
           $setup->getTable('catalog_product_option'),
           'is_special_offer',
           [
               'type'     => Table::TYPE_BOOLEAN,
               'unsigned' => true,
               'nullable' => false,
               'default'  => '0',
               'comment'  => 'Special Offer Flag',
           ]
       );
 
       $setup->getConnection()->addColumn(
           $setup->getTable('catalog_product_option_type_value'),
           'description',
           [
               'type'     => Table::TYPE_TEXT,
               'nullable' => true,
               'default'  => null,
               'comment'  => 'Description',
           ]
       );
 
       $setup->endSetup();
   }
}