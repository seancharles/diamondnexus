<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SharedCatalog\Test\Constraint;

use Magento\SharedCatalog\Test\Page\Adminhtml\SharedCatalogIndex;
use Magento\SharedCatalog\Test\Page\Adminhtml\SharedCatalogConfigure;
use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\SharedCatalog\Test\Fixture\SharedCatalog;

/**
 * Assert steps and titles in Shared Catalog configuration wizard.
 */
class AssertSharedCatalogConfigurationWizard extends AbstractConstraint
{
    /**
     * Wizard title.
     */
    const WIZARD_TITLE = 'Products in Catalog';

    /**
     * Wizard step title.
     */
    const WIZARD_STEP_TITLE = 'Step 1: Select Products for Catalog';

    /**
     * Wizard steps.
     */
    private $wizardSteps = ['Products', 'Pricing'];

    /**
     * Assert that configuration wizard is correct.
     *
     * @param SharedCatalogIndex $sharedCatalogIndex
     * @param SharedCatalogConfigure $sharedCatalogConfigure
     * @param SharedCatalog $sharedCatalog
     * @return void
     */
    public function processAssert(
        SharedCatalogIndex $sharedCatalogIndex,
        SharedCatalogConfigure $sharedCatalogConfigure,
        SharedCatalog $sharedCatalog
    ) {
        $sharedCatalogIndex->open();
        $sharedCatalogIndex->getGrid()->search(['name' => $sharedCatalog->getName()]);
        $sharedCatalogIndex->getGrid()->openConfigure($sharedCatalogIndex->getGrid()->getFirstItemId());
        $sharedCatalogConfigure->getContainer()->openConfigureWizard();
        \PHPUnit\Framework\Assert::assertEquals(
            self::WIZARD_TITLE,
            $sharedCatalogConfigure->getWizard()->getTitle(),
            'Title of the configuration wizard is wrong.'
        );
        \PHPUnit\Framework\Assert::assertEquals(
            self::WIZARD_STEP_TITLE,
            $sharedCatalogConfigure->getWizard()->getStepTitle(),
            'Wizard step title is wrong.'
        );
        \PHPUnit\Framework\Assert::assertEquals(
            $this->wizardSteps,
            $sharedCatalogConfigure->getWizard()->getSteps(),
            'Wizard steps are wrong.'
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Configuration wizard is correct.';
    }
}
