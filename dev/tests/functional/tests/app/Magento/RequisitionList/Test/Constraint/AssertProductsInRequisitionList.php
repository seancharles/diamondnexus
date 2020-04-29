<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\RequisitionList\Test\Constraint;

use Magento\RequisitionList\Test\Page\RequisitionListGrid;
use Magento\RequisitionList\Test\Page\RequisitionListView;
use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Mtf\ObjectManager;

/**
 * Assert that requisition list contains correct products
 */
class AssertProductsInRequisitionList extends AbstractConstraint
{
    /**
     * Assert that requisition list contains correct products
     *
     * @param RequisitionListGrid $requisitionListGrid
     * @param RequisitionListView $requisitionListView
     * @param array $products
     * @param string $name
     * @return void
     */
    public function processAssert(
        RequisitionListGrid $requisitionListGrid,
        RequisitionListView $requisitionListView,
        array $products,
        $name
    ) {
        $requisitionListGrid->open();
        $requisitionListGrid->getRequisitionListGrid()->openRequisitionListByName($name);

        $this->checkProducts($products, $requisitionListView);
        $this->checkQty($products, $requisitionListView);
    }

    /**
     * @param array $products
     * @param RequisitionListView $requisitionListView
     */
    public function checkProducts(array $products, RequisitionListView $requisitionListView)
    {
        $skuArr = [];
        foreach ($products as $product) {
            $skuArr[] = $product->getData('sku');
        }

        $result = array_diff($skuArr, $requisitionListView->getRequisitionListContent()->getSkuList());

        \PHPUnit\Framework\Assert::assertTrue(
            count($result) == 0,
            'Requisition list products are not correct.'
        );
    }

    /**
     * @param array $products
     * @param RequisitionListView $requisitionListView
     */
    public function checkQty(array $products, RequisitionListView $requisitionListView)
    {
        $qtyArray = [];
        foreach ($products as $product) {
            $qtyArray[$product->getSku()] = (int) $product->getCheckoutData()['qty'];
        }

        $result = array_diff($qtyArray, $requisitionListView->getRequisitionListContent()->getQtys());

        \PHPUnit\Framework\Assert::assertTrue(
            count($result) == 0,
            'Product qtys are correct.'
        );
    }

    /**
     * Returns a string representation of the object
     *
     * @return string
     */
    public function toString()
    {
        return 'Requisition list products are correct.';
    }
}
