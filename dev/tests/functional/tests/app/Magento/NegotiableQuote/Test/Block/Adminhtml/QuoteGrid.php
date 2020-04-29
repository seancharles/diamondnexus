<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\NegotiableQuote\Test\Block\Adminhtml;

use Magento\Ui\Test\Block\Adminhtml\DataGrid;

/**
 * Admin Data Grid for managing "Quote" entities.
 */
class QuoteGrid extends DataGrid
{
    /**
     * Locator value for "Edit" link inside action column.
     *
     * @var string
     */
    protected $editLink = '.action-menu-item[href*="view"]';

    /**
     * Filters array mapping.
     *
     * @var array
     */
    protected $filters = [
        'quote_name' => [
            'selector' => '.admin__data-grid-filters input[name*=quote_name]',
        ],
        'status' => [
            'selector' => '[name="status"]',
            'input' => 'select'
        ],
    ];
}
