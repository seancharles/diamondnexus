<?php
namespace ForeverCompanies\CustomLinked\Setup;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
class InstallData implements InstallDataInterface
{
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {

        $data = [
            ['link_type_id' => \ForeverCompanies\CustomLinked\Model\Product\Link::LINK_TYPE_CUSTOMLINKED, 'code' => 'customlinked'],
        ];
        foreach ($data as $bind) {
            $setup->getConnection()
                ->insertForce($setup->getTable('catalog_product_link_type'), $bind);
        }
        $data = [
            [
                'link_type_id' => \ForeverCompanies\CustomLinked\Model\Product\Link::LINK_TYPE_CUSTOMLINKED,
                'product_link_attribute_code' => 'position',
                'data_type' => 'int',
            ]
        ];
        $setup->getConnection()
            ->insertMultiple($setup->getTable('catalog_product_link_attribute'), $data);
    }
}
