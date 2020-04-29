<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SharedCatalog\Test\Block\Adminhtml\Customer;

use Magento\Mtf\Block\Block;
use Magento\Mtf\Client\Locator;

/**
 * Grid header which contains filters.
 */
class GridHeader extends Block
{
    /**
     * Css selector loader.
     *
     * @var string
     */
    private $loader = '[data-role="spinner"]';

    /**
     * Css selector Filters button.
     *
     * @var string
     */
    private $filtersButton = '[data-action="grid-filter-expand"]';

    /**
     * Xpath locator customer group filter options from the shared catalog section.
     *
     * @var string
     */
    private $customerGroupFilterOptions = '//optgroup[@label="Shared Catalogs"]/following-sibling::option';

    /**
     * Expand filters panel.
     *
     * @return void
     */
    public function expandFiltersPanel()
    {
        $this->waitForElementNotVisible($this->loader);
        $this->_rootElement->find($this->filtersButton)->click();
    }

    /**
     * Get list of customer group filter options from the Shared Catalog section.
     *
     * @return array
     */
    public function getCustomerGroupSharedCatalogOptions()
    {
        $filterOptions = [];

        $options = $this->_rootElement->getElements($this->customerGroupFilterOptions, Locator::SELECTOR_XPATH);

        foreach ($options as $option) {
            $filterOptions[] = $option->getText();
        }

        return $filterOptions;
    }
}
