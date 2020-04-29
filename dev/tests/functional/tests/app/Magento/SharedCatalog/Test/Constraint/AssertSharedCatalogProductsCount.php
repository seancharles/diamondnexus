<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SharedCatalog\Test\Constraint;

use Magento\SharedCatalog\Test\Fixture\SharedCatalog;
use Magento\SharedCatalog\Test\Page\Adminhtml\SharedCatalogIndex;
use Magento\SharedCatalog\Test\Page\Adminhtml\SharedCatalogConfigure;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Verify number of products included into shared catalog categories.
 */
class AssertSharedCatalogProductsCount extends AbstractConstraint
{
    /**
     * Assert that shared catalog categories have correct number of products.
     *
     * @param SharedCatalogIndex $sharedCatalogIndex
     * @param SharedCatalogConfigure $sharedCatalogConfigure
     * @param SharedCatalog $sharedCatalog
     * @param array $categoriesStructure
     * @return void
     */
    public function processAssert(
        SharedCatalogIndex $sharedCatalogIndex,
        SharedCatalogConfigure $sharedCatalogConfigure,
        SharedCatalog $sharedCatalog,
        array $categoriesStructure
    ) {
        $sharedCatalogIndex->open();
        $sharedCatalogIndex->getGrid()->search(['name' => $sharedCatalog->getName()]);
        $sharedCatalogIndex->getGrid()->openConfigure($sharedCatalogIndex->getGrid()->getFirstItemId());
        $sharedCatalogConfigure->getContainer()->openConfigureWizard();
        $categoriesTree = $sharedCatalogConfigure->getStructureJstree();
        $categoriesTree->setTreeType('structure')->expandAll();
        $categoriesTree->setTreeType('structure')->expandAll();
        foreach ($categoriesStructure as $category) {
            \PHPUnit\Framework\Assert::assertTrue(
                strpos(
                    $categoriesTree->getProductCount($category['category']->getName()),
                    $category['product_count']
                ) !== false,
                'Shared catalog ' . $sharedCatalog->getName() . ' has wrong number of products in '
                . $category['category']->getName() . '.'
            );
        }
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Shared catalog categories have correct number of products.';
    }
}
