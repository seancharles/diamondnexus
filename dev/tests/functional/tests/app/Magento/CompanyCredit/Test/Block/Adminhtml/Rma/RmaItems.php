<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CompanyCredit\Test\Block\Adminhtml\Rma;

use Magento\Mtf\Block\Block;
use Magento\Mtf\Client\Locator;

/**
 * Class RmaItems.
 */
class RmaItems extends Block
{
    /**
     * Css locator for resolution field.
     *
     * @var string
     */
    private $resolutionField = "#rma_properties_resolution_new0";

    /**
     * Get options of the resolution select.
     *
     * @return array
     */
    public function getResolutionOptions()
    {
        return $this->_rootElement->find($this->resolutionField)->getElements('option');
    }
}
