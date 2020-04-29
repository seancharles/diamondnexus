<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SharedCatalog\Test\Constraint;

use Magento\SharedCatalog\Test\Fixture\SharedCatalog;
use Magento\Catalog\Test\Fixture\Category;
use Magento\SharedCatalog\Test\Page\Adminhtml\SharedCatalogIndex;
use Magento\SharedCatalog\Test\Page\Adminhtml\SharedCatalogConfigure;
use Magento\SharedCatalog\Test\Page\Adminhtml\SharedCatalogCompany;

use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Assert duplicate shared catalog shows in grid.
 */
class AssertDuplicateSharedCatalog extends AbstractConstraint
{
    /**
     * Assert duplicate shared catalog shows in grid.
     *
     * @param SharedCatalogIndex $sharedCatalogIndex
     * @param SharedCatalogCompany $sharedCatalogCompany
     * @param SharedCatalogConfigure $sharedCatalogConfigure
     * @param SharedCatalog $sharedCatalog
     * @param Category $category
     * @param array products
     * @return void
     */
    public function processAssert(
        SharedCatalogIndex $sharedCatalogIndex,
        SharedCatalogCompany $sharedCatalogCompany,
        SharedCatalogConfigure $sharedCatalogConfigure,
        SharedCatalog $sharedCatalog,
        Category $category,
        array $products
    ) {
        $dubbedNameSharedCatalog = 'Duplicate of ' . $sharedCatalog->getName();
        $sharedCatalogIndex->open();
        $sharedCatalogIndex->getGrid()->search(['name' => $dubbedNameSharedCatalog]);
        $sharedCatalogId = $sharedCatalogIndex->getGrid()->getFirstItemId();

        \PHPUnit\Framework\Assert::assertTrue(
            $sharedCatalogIndex->getGrid()->isRowVisible(['name' => $dubbedNameSharedCatalog]),
            'Shared catalog \'' . $dubbedNameSharedCatalog . '\' isn\'t present in pages grid.'
        );

        $sharedCatalogIndex->getGrid()->openCompanies($sharedCatalogId);
        $sharedCatalogCompany->getCompanyGrid()->search(['is_current' => 'Yes']);

        \PHPUnit\Framework\Assert::assertTrue(
            empty($sharedCatalogCompany->getCompanyGrid()->getAllIds()),
            'Shared catalog \'' . $dubbedNameSharedCatalog . '\' Companies is not empty.'
        );

        $sharedCatalogIndex->open();
        $sharedCatalogIndex->getGrid()->openConfigure($sharedCatalogId);
        $sharedCatalogConfigure->getContainer()->openConfigureWizard();
        $categoriesTree = $sharedCatalogConfigure->getStructureJstree();
        $categoriesTree->setTreeType('structure')->expandAll();
        $categoriesTree->setTreeType('structure')->expandAll();

        \PHPUnit\Framework\Assert::assertTrue(
            strpos(
                $categoriesTree->getProductCount($category->getName()),
                count($products) . ' of ' . count($products) . ' included'
            ) !== false,
            'Shared catalog ' . $dubbedNameSharedCatalog . ' has wrong number of products in '
            . $category->getName() . '.'
        );

        foreach ($products as $product) {
            \PHPUnit\Framework\Assert::assertTrue(
                $sharedCatalogConfigure->getStructureGrid()->isSelectedItem(['sku' => $product->getSku()]),
                'Product ' . $product->getSku() . ' is not assigned.'
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
        return 'Duplicate of Shared catalog was created.';
    }
}
