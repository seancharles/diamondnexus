<?php

namespace ForeverCompanies\CustomAttributes\Setup\Patch\Data;

use Magento\Catalog\Model\Product;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\App\ResourceConnection;

class UpdateAttributeSetGroupsPrice implements DataPatchInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private ModuleDataSetupInterface $moduleDataSetup;

    /**
     * @var EavSetupFactory
     */
    private EavSetupFactory $eavSetupFactory;

    protected $resourceConnection;

    /**
     * Constructor
     *
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param EavSetupFactory $eavSetupFactory
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        EavSetupFactory $eavSetupFactory,
        ResourceConnection $resourceConnection
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->eavSetupFactory = $eavSetupFactory;
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * {@inheritdoc}
     * @return void
     */
    public function apply()
    {
        $this->moduleDataSetup->getConnection()->startSetup();

        $connection = $this->resourceConnection->getConnection();

        $query = "SELECT
                    a.entity_attribute_id,
                    g.attribute_group_id
                FROM
                    eav_entity_attribute a
                INNER JOIN
                    eav_attribute_group g ON a.attribute_set_id = g.attribute_set_id AND g.attribute_group_code = 'general'
                WHERE
                    a.attribute_id = 254;";

        $result = $connection->fetchAll($query);

        foreach($result as $row) {
            $connection->query("UPDATE
                                    eav_entity_attribute
                                SET
                                    attribute_group_id = " . (int) $row['attribute_group_id'] . "
                                WHERE
                                    entity_attribute_id = " . (int) $row['entity_attribute_id'] . ";" );
        }

        $this->moduleDataSetup->getConnection()->endSetup();
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases(): array
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies(): array
    {
        return [];
    }

    /**
     * Module version
     */
    public static function getVersion(): string
    {
        return '0.0.1';
    }
}
