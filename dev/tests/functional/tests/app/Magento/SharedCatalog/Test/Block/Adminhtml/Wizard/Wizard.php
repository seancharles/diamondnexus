<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SharedCatalog\Test\Block\Adminhtml\Wizard;

use Magento\Mtf\Block\Block;

/**
 * Configuration wizard modal window.
 */
class Wizard extends Block
{
    /**
     * Selector for title element.
     *
     * @var string
     */
    private $title = '[data-role="title"]';

    /**
     * Selector for steps navigation elements.
     *
     * @var string
     */
    private $stepsNavigation = '.steps-wizard-navigation a';

    /**
     * Selector for step title.
     *
     * @var string
     */
    private $stepTitle = '.steps-wizard-title';

    /**
     * Wait for modal window show
     *
     * @return void
     */
    public function waitForLoad()
    {
        $this->waitForElementVisible($this->_rootElement->getAbsoluteSelector());
    }

    /**
     * Get title of the wizard.
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->_rootElement->find($this->title)->getText();
    }

    /**
     * Get steps of the wizard.
     *
     * @return array
     */
    public function getSteps()
    {
        return array_map(
            function ($step) {
                return $step->getText();
            },
            $this->_rootElement->getElements($this->stepsNavigation)
        );
    }

    /**
     * Get step title.
     *
     * @return string
     */
    public function getStepTitle()
    {
        return $this->_rootElement->find($this->stepTitle)->getText();
    }
}
