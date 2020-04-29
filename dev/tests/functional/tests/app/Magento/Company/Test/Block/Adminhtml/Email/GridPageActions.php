<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Company\Test\Block\Adminhtml\Email;

use Magento\Backend\Test\Block\GridPageActions as AbstractGridPageActions;

/**
 * Grid page actions block.
 */
class GridPageActions extends AbstractGridPageActions
{
    /**
     * "Add New" button.
     *
     * @var string
     */
    protected $addNewButton = "[data-ui-id='adminhtml-system-email-template-container-add-button']";
}
