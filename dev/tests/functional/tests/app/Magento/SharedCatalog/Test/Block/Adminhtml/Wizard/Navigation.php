<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SharedCatalog\Test\Block\Adminhtml\Wizard;

use Magento\Mtf\Block\Block;

/**
 * Wizard Navigation
 */
class Navigation extends Block
{
    /** @var string */
    protected $previousButton = '.action-back-step';

    /** @var string */
    protected $nextButton = '.action-next-step';

    /** @var string */
    protected $cancelButton = '.action-cancel';

    /*
     * Next Step
     *
     * @return void
     */
    public function nextStep()
    {
        $this->_rootElement->find($this->nextButton)->click();
    }

    /*
     * Cancel Wizard
     *
     * @return void
     */
    public function cancelWizard()
    {
        $this->_rootElement->find($this->cancelButton)->click();
    }

    /*
     * Prev Step
     *
     * @return void
     */
    public function prevStep()
    {
        $this->_rootElement->find($this->previousButton)->click();
    }
}
