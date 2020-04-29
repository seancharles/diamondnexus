<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Company\Test\Block\Adminhtml\Edit\Tab\CustomerView;

use Magento\Mtf\Client\Element\SimpleElement;
use Magento\Mtf\Client\Locator;

/**
 * Class CompanyName
 * Get company name on the customer form
 */
class CompanyName extends SimpleElement
{
    /**
     * Name locator
     *
     * @var string
     */
    protected $value = 'table/tbody/tr[2]/td';

    /**
     * Get value
     *
     * @return string
     */
    public function getValue()
    {
        return $this->find($this->value, Locator::SELECTOR_XPATH)->getText();
    }
}
