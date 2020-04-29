<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Company\Test\Block\Adminhtml;

use Magento\Ui\Test\Block\Adminhtml\DataGrid;
use Magento\Mtf\Client\Locator;

/**
 * Class CompanyCustomerGrid.
 */
class CompanyCustomerGrid extends DataGrid
{
    /**
     * Column header label Xpath locator.
     *
     * @var string
     */
    protected $cellHeaderLabel = '//table[@data-role="grid"]/thead/tr/th/span[text()="%s"]';

    /**
     * Grid row Xpath locator.
     *
     * @var string
     */
    protected $rowById = "//input[@data-action='select-row' and @value='%s']/parent::label/parent::td/parent::tr";

    /**
     * Css selector for company filter field.
     *
     * @var string
     */
    protected $companyFilterField = 'input[name="company_name"]';

    /**
     * Filters.
     *
     * @var array
     */
    protected $filters = [
        'company_name' => [
            'selector' => '.admin__data-grid-filters input[name*=company_name]'
        ]
    ];

    /**
     * Check if column is visible in customer grid.
     *
     * @param string $columnHeader
     * @return bool
     */
    public function isColumnVisible($columnHeader)
    {
        $this->waitLoader();
        return $this->_rootElement
            ->find(sprintf($this->cellHeaderLabel, $columnHeader), Locator::SELECTOR_XPATH)
            ->isVisible();
    }

    /**
     * Check if company filter field is visible in customer grid.
     *
     * @return bool
     */
    public function isCompanyFilterVisible()
    {
        $this->openFilterBlock();
        return $this->_rootElement->find($this->companyFilterField)->isVisible();
    }
}
