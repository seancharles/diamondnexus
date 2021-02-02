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

class SalesOrderGridSalesPerson implements DataPatchInterface
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
        $updateQuery = "UPDATE
                            sales_order,
                            sales_order_grid
                        SET
                            sales_order_grid.sales_person_id = sales_order.sales_person_id
                        WHERE
                            sales_order.entity_id = sales_order_grid.entity_id;";
        
        $this->connection->query($updateQuery);
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
