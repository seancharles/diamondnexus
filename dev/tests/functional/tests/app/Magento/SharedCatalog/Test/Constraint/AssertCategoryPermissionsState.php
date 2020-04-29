<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SharedCatalog\Test\Constraint;

use Magento\SharedCatalog\Test\Page\Adminhtml\SystemConfigCatalog;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Check that system configuration category permissions controls state is correct.
 */
class AssertCategoryPermissionsState extends AbstractConstraint
{
    /**
     * System configuration Category Permissions Enabled option text.
     *
     * @var string
     */
    private $categoryPermissionsEnabledOptionText = 'Yes';

    /**
     * System configuration allowed for everyone option text.
     *
     * @var string
     */
    private $allowedForEveryoneOptionText = 'Yes, for Everyone';

    /**
     * Check that system configuration category permissions controls state is correct.
     *
     * @param SystemConfigCatalog $systemConfigCatalog
     * @return void
     */
    public function processAssert(SystemConfigCatalog $systemConfigCatalog)
    {
        $systemConfigCatalog->open();
        $systemConfigCatalog->getCatalog()->openCategoryPermissionsSection();

        \PHPUnit\Framework\Assert::assertTrue(
            $systemConfigCatalog->getCatalog()->isCategoryPermissionsEnableControlDisabled(),
            'Category Permissions Enabled control is enabled.'
        );
        \PHPUnit\Framework\Assert::assertEquals(
            $this->categoryPermissionsEnabledOptionText,
            $systemConfigCatalog->getCatalog()->getCategoryPermissionsEnableValue(),
            'Category Permissions are disabled.'
        );
        \PHPUnit\Framework\Assert::assertEquals(
            $this->allowedForEveryoneOptionText,
            $systemConfigCatalog->getCatalog()->getCategoryPermissionsAllowBrowsingCategoryValue(),
            'Category Permissions Allow Browsing Category value is incorrect.'
        );
        \PHPUnit\Framework\Assert::assertEquals(
            $this->allowedForEveryoneOptionText,
            $systemConfigCatalog->getCatalog()->getCategoryPermissionsDisplayProductPricesValue(),
            'Category Permissions Display Product Prices value is incorrect.'
        );
        \PHPUnit\Framework\Assert::assertEquals(
            $this->allowedForEveryoneOptionText,
            $systemConfigCatalog->getCatalog()->getCategoryPermissionsAllowAddingToCartValue(),
            'Category Permissions Allow Adding to Cart value is incorrect.'
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Category permissions settings are correct.';
    }
}
