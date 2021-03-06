<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace ForeverCompanies\CustomSales\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\App\ResourceConnection;

class OrderIncrementsPatch implements DataPatchInterface
{
    protected $resource;
    protected $connection;

    /**
     * Constructor
     *
     * @param ResourceConnection $resource
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
        $this->updateEntities(5, 'order');
        $this->updateEntities(6, 'invoice');
        $this->updateEntities(7, 'creditmemo');
        $this->updateEntities(8, 'shipment');
    }

    /**
     * @param int $typeId
     * @param string|null $typeName
     */
    protected function updateEntities($typeId = 0, $typeName = null)
    {
        $eavEntityStoreData = $this->getTableData('eav_entity_store', 'entity_type_id='.$typeId);

        foreach ($eavEntityStoreData as $store) {
            $storeId = $store['store_id'];
            $prefix = $store['increment_prefix'];

            $condition = 'entity_type="' . $typeName . '" AND store_id=' . $storeId;
            $sequenceMetaData = $this->getTableData('sales_sequence_meta', $condition);

            if (isset($sequenceMetaData[0]) == true) {
                $metaId = $sequenceMetaData[0]['meta_id'];

                $updateQuery = "UPDATE ".$this->connection->getTableName("sales_sequence_profile").
                    " SET prefix = '".$prefix."'".
                    " WHERE meta_id = '".$metaId."';";

                $this->connection->query($updateQuery);
            }
        }
    }

    /**
     * @param string|null $tableName
     * @param string|null $condition
     * @return array
     */
    protected function getTableData($tableName = null, $condition = null)
    {
        $tableName = $this->connection->getTableName($tableName);
        $select = $this->connection->select()->from($tableName);
        if ($condition !== null && $condition !== '') {
            $select->where($condition);
        }

        return $this->connection->fetchAll($select);
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
