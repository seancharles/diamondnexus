<?php

namespace ForeverCompanies\StonesIntermediary\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

/**
 * @api
 * @since 100.0.2
 */
interface StonesSupplierSearchResultsInterface extends SearchResultsInterface
{
    /**
     * Get attributes list.
     *
     * @return \ForeverCompanies\StonesIntermediary\Api\Data\StonesSupplierInterface[]
     */
    public function getItems();

    /**
     * Set attributes list.
     *
     * @param \ForeverCompanies\StonesIntermediary\Api\Data\StonesSupplierInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
