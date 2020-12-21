<?php

namespace ForeverCompanies\CustomApi\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

/**
 * @api
 * @since 100.0.2
 */
interface ExtSearchResultsInterface extends SearchResultsInterface
{
    /**
     * Get attributes list.
     *
     * @return \ForeverCompanies\CustomApi\Api\Data\ExtSalesOrderUpdateInterface[]
     */
    public function getItems();

    /**
     * Set attributes list.
     *
     * @param \ForeverCompanies\CustomApi\Api\Data\ExtSalesOrderUpdateInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
