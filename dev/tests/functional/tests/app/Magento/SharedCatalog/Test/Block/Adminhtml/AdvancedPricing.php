<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SharedCatalog\Test\Block\Adminhtml;

use Magento\Mtf\Block\Block;

/**
 * Advanced pricing popup block.
 */
class AdvancedPricing extends Block
{
    /**
     * Css selector Done button.
     *
     * @var string
     */
    private $doneButton = '.action-primary[data-role="action"]';

    /**
     * Save advanced pricing data.
     *
     * @return void
     */
    public function save()
    {
        $this->_rootElement->find($this->doneButton)->click();
    }
}
