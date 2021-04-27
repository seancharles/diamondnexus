<?php
/**
 * Copyright © ForeverCompanies LLC. All rights reserved.
 * See COPYING.txt for license details.
 * http://www.forevercompanies.com | support@forevercompanies.com
 */

namespace ForeverCompanies\LinkProduct\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use ForeverCompanies\LinkProduct\Model\Product\Link;
use ForeverCompanies\LinkProduct\Ui\DataProvider\Product\Form\Modifier\Accessory;

class InstallSchema implements InstallSchemaInterface
{

    /**
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        /**
         * Install product link types in table (catalog_product_link_type)
         */
        $catalogProductLinkTypeData = [
            'link_type_id' => Link::LINK_TYPE_ACCESSORY,
            'code' => Accessory::DATA_SCOPE_ACCESSORY
        ];

        $setup->getConnection()->insertOnDuplicate(
            $setup->getTable('catalog_product_link_type'),
            $catalogProductLinkTypeData
        );

        /**
         * install product link attributes position in table catalog_product_link_attribute
         */
        $catalogProductLinkAttributeData = [
            'link_type_id' => Link::LINK_TYPE_ACCESSORY,
            'product_link_attribute_code' => 'position',
            'data_type' => 'int',
        ];

        $setup->getConnection()->insert(
            $setup->getTable('catalog_product_link_attribute'),
            $catalogProductLinkAttributeData
        );

        $setup->endSetup();
    }
}
