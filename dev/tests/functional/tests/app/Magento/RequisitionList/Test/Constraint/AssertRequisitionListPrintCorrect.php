<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\RequisitionList\Test\Constraint;

use Magento\RequisitionList\Test\Page\RequisitionListGrid;
use Magento\RequisitionList\Test\Page\RequisitionListView;
use Magento\RequisitionList\Test\Page\RequisitionListPrintView;
use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Mtf\Client\BrowserInterface;

/**
 * Check that Requisition List print contains correct data.
 */
class AssertRequisitionListPrintCorrect extends AbstractConstraint
{
    /**
     * Check that Requisition List print contains correct data.
     *
     * @param RequisitionListGrid $requisitionListGrid
     * @param RequisitionListView $requisitionListView
     * @param RequisitionListPrintView $requisitionListPrintView
     * @param BrowserInterface $browser
     * @param array $products
     * @param string $name
     * @return void
     */
    public function processAssert(
        RequisitionListGrid $requisitionListGrid,
        RequisitionListView $requisitionListView,
        RequisitionListPrintView $requisitionListPrintView,
        BrowserInterface $browser,
        array $products,
        $name
    ) {
        $requisitionListGrid->open();
        $requisitionListGrid->getRequisitionListGrid()->openFirstItem();
        $requisitionListView->getRequisitionListContent()->clickPrint();
        $browser->selectWindow();
        $printWindow = $browser->getCurrentWindow();
        $requisitionListPrintView->getPrintContent()->waitForRequisitionListNameBlock($name);
        $this->checkProducts($products, $requisitionListPrintView);
        $this->checkLogo($requisitionListPrintView);
        $this->checkRequisitionListName($name, $requisitionListPrintView);
        $browser->closeWindow();
        if (in_array($printWindow, $browser->getWindowHandles())) {
            $browser->closeWindow($printWindow);
        }
    }

    /**
     * @param array $products
     * @param RequisitionListPrintView $requisitionListPrintView
     */
    protected function checkProducts(array $products, RequisitionListPrintView $requisitionListPrintView)
    {
        $skuArr = [];
        foreach ($products as $product) {
            $skuArr[] = $product->getData('sku');
        }
        $result = array_diff($skuArr, $requisitionListPrintView->getPrintContent()->getSkuList());

        \PHPUnit\Framework\Assert::assertTrue(
            count($result) == 0,
            'Requisition list products are not correct.'
        );
    }

    /**
     * @param RequisitionListPrintView $requisitionListPrintView
     */
    public function checkLogo(RequisitionListPrintView $requisitionListPrintView)
    {
        \PHPUnit\Framework\Assert::assertTrue(
            $requisitionListPrintView->getPrintContent()->isLogoVisible(),
            'Logo is not visible.'
        );
    }

    /**
     * @param string $name
     * @param RequisitionListPrintView $requisitionListPrintView
     */
    public function checkRequisitionListName($name, RequisitionListPrintView $requisitionListPrintView)
    {
        \PHPUnit\Framework\Assert::assertEquals(
            $requisitionListPrintView->getPrintContent()->getRequisitionListName(),
            $name,
            'Requisition list name is wrong.'
        );
    }

    /**
     * Returns a string representation of the object
     *
     * @return string
     */
    public function toString()
    {
        return 'Requisition list print version is correct.';
    }
}
