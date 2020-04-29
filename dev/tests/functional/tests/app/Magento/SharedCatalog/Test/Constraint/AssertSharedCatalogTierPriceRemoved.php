<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SharedCatalog\Test\Constraint;

use Magento\SharedCatalog\Test\Page\Adminhtml\SharedCatalogIndex;
use Magento\SharedCatalog\Test\Page\Adminhtml\SharedCatalogConfigure;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Assert tier price was disappeared in shared catalog Advanced Pricing popup.
 */
class AssertSharedCatalogTierPriceRemoved extends AbstractConstraint
{
    /**
     * Assert tier price was disappeared in shared catalog Advanced Pricing popup.
     *
     * @param SharedCatalogIndex $sharedCatalogIndex
     * @param SharedCatalogConfigure $sharedCatalogConfigure
     * @param string $sku
     * @param string $sharedCatalogName
     */
    public function processAssert(
        SharedCatalogIndex $sharedCatalogIndex,
        SharedCatalogConfigure $sharedCatalogConfigure,
        $sku,
        $sharedCatalogName
    ) {
        $sharedCatalogIndex->open();
        $sharedCatalogIndex->getGrid()->search(['name' => $sharedCatalogName]);
        $sharedCatalogIndex->getGrid()->openConfigure($sharedCatalogIndex->getGrid()->getFirstItemId());
        $sharedCatalogConfigure->getContainer()->openConfigureWizard();
        $sharedCatalogConfigure->getNavigation()->nextStep();
        $sharedCatalogConfigure->getPricingGrid()->search(['sku' => $sku]);
        $sharedCatalogConfigure->getPricingGrid()->openTierPriceConfiguration();

        \PHPUnit\Framework\Assert::assertTrue(
            !$sharedCatalogConfigure->getTierPriceModal()->isTierPriceOptionsPresent(),
            'Tier price options are present in shared catalog advanced pricing options.'
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Tier price options are not present in shared catalog advanced pricing options.';
    }
}
