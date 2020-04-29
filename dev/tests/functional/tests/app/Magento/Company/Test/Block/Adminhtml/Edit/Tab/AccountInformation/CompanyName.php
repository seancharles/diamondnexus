<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Company\Test\Block\Adminhtml\Edit\Tab\AccountInformation;

use Magento\Mtf\Client\Element\SuggestElement;

/**
 * Class CompanyName
 * Set company association on the customer form
 */
class CompanyName extends SuggestElement
{
    /**
     * @inheritdoc
     */
    protected $closeButton = 'button[data-action="done-select"]';

    /**
     * @inheritdoc
     */
    protected $searchLabel = '.admin__action-multiselect-search-label';

    /**
     * @inheritdoc
     */
    protected $resultItem = '//ul/li[contains(., "%s")]';

    /**
     * @inheritdoc
     */
    protected $advancedSelect = '[data-action="open-search"]';

    /**
     * @inheritdoc
     */
    protected $selectInput = '[data-action="input-text"]';

    /**
     * @inheritdoc
     */
    protected $searchedCount = '[class*=results-amount]';
}
