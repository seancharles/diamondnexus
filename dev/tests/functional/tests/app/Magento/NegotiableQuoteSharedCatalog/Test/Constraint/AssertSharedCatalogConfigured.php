<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\NegotiableQuoteSharedCatalog\Test\Constraint;

use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\SharedCatalog\Test\Page\Adminhtml\SharedCatalogIndex;

/**
 * Class AssertSharedCatalogConfigured
 * Assert that success message is correct
 */
class AssertSharedCatalogConfigured extends AbstractConstraint
{
    /**
     * Message about successfully applied changes to the Shared Catalog configuration
     */
    const SUCCESS_MESSAGE = 'The selected changes have been applied to the shared catalog.';

    /**
     * Assert that success message is displayed after Shared Catalog configuration changes were applied.
     *
     * @param SharedCatalogIndex $sharedCatalogIndex
     * @return void
     */
    public function processAssert(SharedCatalogIndex $sharedCatalogIndex)
    {
        $actualMessage = $sharedCatalogIndex->getMessages()->getSuccessMessage();
        \PHPUnit\Framework\Assert::assertEquals(
            self::SUCCESS_MESSAGE,
            $actualMessage,
            'Wrong success message is displayed.'
            . "\nExpected: " . self::SUCCESS_MESSAGE
            . "\nActual: " . $actualMessage
        );
    }

    /**
     * Text success save message is displayed
     *
     * @return string
     */
    public function toString()
    {
        return 'Assert that success message is displayed.';
    }
}
