<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SharedCatalog\Test\Constraint;

use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Class AbstractLayeredNavigationAssert.
 */
abstract class AbstractLayeredNavigationAssert extends AbstractConstraint
{
    /**
     * toString.
     *
     * @return string
     */
    public function toString()
    {
        return 'Layered navigation filter items content is correct.';
    }

    /**
     * Assert filter item text.
     *
     * @param string $expectedValue
     * @param string $value
     * @return void
     */
    protected function assertFilterItemText($expectedValue, $value)
    {
        \PHPUnit\Framework\Assert::assertEquals(
            $expectedValue,
            $value,
            'Filter item text is incorrect.'
        );
    }
}
