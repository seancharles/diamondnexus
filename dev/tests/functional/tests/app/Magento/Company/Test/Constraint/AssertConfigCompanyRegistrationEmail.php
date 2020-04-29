<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Company\Test\Constraint;

use Magento\Company\Test\Page\Adminhtml\ConfigCompanySetup;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Assert custom template is disabled.
 */
class AssertConfigCompanyRegistrationEmail extends AbstractConstraint
{
    /**
     * Assert custom template is disabled.
     *
     * @param ConfigCompanySetup $configCompanySetup
     * @param string $templateCode
     */
    public function processAssert(
        ConfigCompanySetup $configCompanySetup,
        $templateCode
    ) {
        $configCompanySetup->open();
        \PHPUnit\Framework\Assert::assertTrue(
            $configCompanySetup->getFormEmailOptions()->issetTemplate($templateCode),
            'Custom template is not disabled.'
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Custom template is disabled.';
    }
}
