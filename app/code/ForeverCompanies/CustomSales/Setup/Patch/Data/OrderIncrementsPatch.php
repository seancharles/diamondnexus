<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace ForeverCompanies\CustomSales\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchRevertableInterface;
use Magento\Framework\App\ResourceConnection;

class OrderIncrementsPatch implements DataPatchInterface
{
    protected $resource;
    protected $connection;
    
    /**
     * Constructor
     *
     * @param Config              $eavConfig
     * @param EavSetupFactory     $eavSetupFactory
     * @param AttributeSetFactory $attributeSetFactory
     */
    public function __construct(
        ResourceConnection $resource
    ) {
        $this->resource = $resource;
        $this->connection = $this->resource->getConnection(ResourceConnection::DEFAULT_CONNECTION);
    }

    /**
     * {@inheritdoc}
     */
    public function apply(): void
    {
        /*
            UPDATE `paulthree_magento`.`sales_sequence_profile` SET `prefix` = '11' WHERE (`profile_id` = '3');
            UPDATE `paulthree_magento`.`sales_sequence_profile` SET `prefix` = '14' WHERE (`profile_id` = '5');
            UPDATE `paulthree_magento`.`sales_sequence_profile` SET `prefix` = '17' WHERE (`profile_id` = '6');

            UPDATE `paulthree_magento`.`sales_sequence_profile` SET `prefix` = '20' WHERE (`profile_id` = '13');
            UPDATE `paulthree_magento`.`sales_sequence_profile` SET `prefix` = '24' WHERE (`profile_id` = '14');
            UPDATE `paulthree_magento`.`sales_sequence_profile` SET `prefix` = '21' WHERE (`profile_id` = '15');
            UPDATE `paulthree_magento`.`sales_sequence_profile` SET `prefix` = '27' WHERE (`profile_id` = '16');

            UPDATE `paulthree_magento`.`sales_sequence_profile` SET `prefix` = '30' WHERE (`profile_id` = '9');
            UPDATE `paulthree_magento`.`sales_sequence_profile` SET `prefix` = '31' WHERE (`profile_id` = '10');
            UPDATE `paulthree_magento`.`sales_sequence_profile` SET `prefix` = '34' WHERE (`profile_id` = '11');
        */
        
        // pull order increment ids, we are leaving other entities
       $eavEntityStoreData = $this->getTableData('eav_entity_store','entity_type_id=5');
       
       // maps values from the m1 table eav_entity_store and sets them to the new m2 eqivalent sales_sequence_profile
       foreach($eavEntityStoreData as $store)
        {
            $storeId = $store['store_id'];
            $prefix = $store['store_id'];
           
            $sequenceMetaData = $this->getTableData('sales_sequence_meta','entity_type="order" AND store_id='.$storeId);
           
            if(isset($sequenceMetaData[0]) == true) {
                
                $metaId = $sequenceMetaData[0]['meta_id'];
           
                $updateQuery = "UPDATE ".$this->connection->getTableName("sales_sequence_profile").
                    " SET prefix = ".$prefix.
                    " WHERE meta_id = '".$metaId."';";
            }
        }
    }

    protected function getTableData($tableName = null, $condition = null)
    {
        $tableName = $this->connection->getTableName($tableName);
        
        if($condition) {
            $sql = "SELECT * FROM `".$tableName."` WHERE ".$condition.";";
        } else {
            $sql = "SELECT * FROM `".$tableName."`;";
        }
        
        return $this->connection->fetchAll($sql);
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies(): array
    {
        return [];
    }
    
    /**
     * {@inheritdoc}
     */
    public function getAliases(): array
    {
        return [];
    }
}
