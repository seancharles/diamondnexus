<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SharedCatalog\Test\Constraint;

use Magento\SharedCatalog\Test\Fixture\SharedCatalog;
use Magento\Catalog\Test\Page\Adminhtml\CatalogProductIndex;
use Magento\SharedCatalog\Test\Page\Adminhtml\SharedCatalogIndex;
use Magento\SharedCatalog\Test\Page\Adminhtml\SharedCatalogConfigure;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Assert list of fields is correct in "Columns" panel.
 */
class AssertFieldsInColumnsPanel extends AbstractConstraint
{
    /**
     * Assert list of fields is correct in "Columns" panel.
     *
     * @param SharedCatalogIndex $sharedCatalogIndex
     * @param SharedCatalogConfigure $sharedCatalogConfigure
     * @param SharedCatalog $sharedCatalog
     * @param CatalogProductIndex $catalogProductIndex
     * @param string $checkedFieldsInColumnsPanel
     * @param string $uncheckedFieldsInColumnsPanel
     * @param string $missingFieldsInColumnsPanel
     * @return void
     */
    public function processAssert(
        SharedCatalogIndex $sharedCatalogIndex,
        SharedCatalogConfigure $sharedCatalogConfigure,
        SharedCatalog $sharedCatalog,
        CatalogProductIndex $catalogProductIndex,
        $checkedFieldsInColumnsPanel,
        $uncheckedFieldsInColumnsPanel,
        $missingFieldsInColumnsPanel
    ) {
        $catalogProductIndex->open();
        $fieldsCatalogPage = $catalogProductIndex->getProductGrid()->getFieldsFromColumnsPanel();
        $sharedCatalogIndex->open();
        $sharedCatalogIndex->getGrid()->search(['name' => $sharedCatalog->getName()]);
        $sharedCatalogIndex->getGrid()->openConfigure($sharedCatalogIndex->getGrid()->getFirstItemId());
        $sharedCatalogConfigure->getContainer()->openConfigureWizard();
        $sharedCatalogConfigure->getStructureGrid()->clickColumnsButton();
        $fieldsSharedCatalogPage = $sharedCatalogConfigure->getStructureGrid()->getFieldsFromColumnsPanel();
        $missingFieldsInColumnsPanel = explode(', ', $missingFieldsInColumnsPanel);
        $diff = array_diff($fieldsCatalogPage, $fieldsSharedCatalogPage + $missingFieldsInColumnsPanel);
        \PHPUnit\Framework\Assert::assertTrue(
            empty($diff),
            'List of fields in "Columns" panel on Catalog Product Grid page is equal 
             to list on Shared Catalog Product Grid page: ' . implode(',', $diff)
        );
        $checkedFieldsInColumnsPanel = explode(',', $checkedFieldsInColumnsPanel);
        foreach ($checkedFieldsInColumnsPanel as $checkedField) {
            $field = $sharedCatalogConfigure->getStructureGrid()->retrieveField(trim($checkedField));
            $checkbox = $field->find('[type="checkbox"]');
            \PHPUnit\Framework\Assert::assertTrue(
                $checkbox->isSelected(),
                trim($checkedField) . ' field in "Columns" panel is not checked.'
            );
        }
        $uncheckedFieldsInColumnsPanel = explode(',', $uncheckedFieldsInColumnsPanel);
        foreach ($uncheckedFieldsInColumnsPanel as $uncheckedField) {
            $field = $sharedCatalogConfigure->getStructureGrid()->retrieveField(trim($uncheckedField));
            $checkbox = $field->find('[type="checkbox"]');
            \PHPUnit\Framework\Assert::assertFalse(
                $checkbox->isSelected(),
                trim($uncheckedField) . ' field in "Columns" panel is checked.'
            );
        }
        foreach ($missingFieldsInColumnsPanel as $missingField) {
            $field = $sharedCatalogConfigure->getStructureGrid()->retrieveField(trim($missingField));
            \PHPUnit\Framework\Assert::assertFalse(
                $field->isPresent(),
                trim($missingField) . ' should not be present in "Columns" Panel.'
            );
        }
        $sharedCatalogConfigure->getStructureGrid()->clickColumnsButton();
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'List of fields in "Columns" panel is correct.';
    }
}
