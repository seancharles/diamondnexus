<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\QuickOrder\Test\Constraint;

use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\QuickOrder\Test\Page\QuickOrder as QuickOrderPage;

/**
 * Class AssertQuickOrderItems
 */
class AssertQuickOrderItems extends AbstractConstraint
{
    /**
     * Assert items list
     *
     * @param QuickOrderPage $quickOrderPage
     * @param array $validProducts
     * @param array $invalidProductSkus
     */
    public function processAssert(
        QuickOrderPage $quickOrderPage,
        array $validProducts,
        array $invalidProductSkus
    ) {
        $itemsBlocks = $quickOrderPage->getItems()->getItemsBlocks();

        foreach ($itemsBlocks as $itemBlock) {
            $itemBlock->waitResultVisible();

            if ($itemBlock->getError()) {
                \PHPUnit\Framework\Assert::assertContains(
                    $itemBlock->getSku(),
                    $invalidProductSkus,
                    'Invalid product in quick order list'
                );
            } else {
                $this->assertValidProductItem($validProducts, $itemBlock);
            }
        }

        \PHPUnit\Framework\Assert::assertEquals(
            count($itemsBlocks),
            count($validProducts) + count($invalidProductSkus),
            'Invalid products in quick order list'
        );
    }

    /**
     * Assert valid product item
     *
     * @param array $validProducts
     * @param \Magento\QuickOrder\Test\Block\Items\Item $itemBlock
     */
    private function assertValidProductItem($validProducts, \Magento\QuickOrder\Test\Block\Items\Item $itemBlock)
    {
        $products = array_filter($validProducts, function ($product) use ($itemBlock) {
            return $product->getSku() == $itemBlock->getSku();
        });
        $product = current($products);

        \PHPUnit\Framework\Assert::assertEquals(
            $product->getName(),
            $itemBlock->getPreviewProductName(),
            'Invalid product name in quick order list'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function toString()
    {
        return 'Quick order product list incorrect.';
    }
}
