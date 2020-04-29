<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SharedCatalog\Test\Constraint;

use Magento\SharedCatalog\Test\Page\Adminhtml\SystemConfigBtob;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Check that system configuration B2B features section description is correct.
 */
class AssertSystemConfigBtobSectionDescription extends AbstractConstraint
{
    /**
     * Check that system configuration B2B features section description is correct.
     *
     * @param SystemConfigBtob $systemConfigBtob
     * @param string $sectionDescription
     * @return void
     */
    public function processAssert(SystemConfigBtob $systemConfigBtob, $sectionDescription)
    {
        \PHPUnit\Framework\Assert::assertEquals(
            $sectionDescription,
            $systemConfigBtob->getBtobFeatures()->getSectionDescription(),
            'System configuration B2B section description is incorrect.'
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'System configuration B2B section description is correct.';
    }
}
