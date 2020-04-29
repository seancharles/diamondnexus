<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Company\Test\Block;

use Magento\Mtf\Block\Form;
use Magento\Mtf\Fixture\FixtureInterface;
use Magento\Mtf\Client\Element\SimpleElement;

/**
 * Abstract popup block
 */
abstract class AbstractPopup extends Form
{
    /**
     * Primary button selector
     *
     * @var string
     */
    protected $primaryButton = '.action.primary';

    /**
     * Loader selector
     *
     * @var string
     */
    protected $loadingMask = '.loading-mask';

    /**
     * Click primary button
     */
    public function submit()
    {
        $this->_rootElement->find($this->primaryButton)->click();
        $this->waitForElementNotVisible($this->loadingMask);
    }
}
