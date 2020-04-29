<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SharedCatalog\Test\Constraint;

use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Catalog\Test\Page\Product\CatalogProductView;
use Magento\Cms\Test\Page\CmsIndex;
use Magento\Mtf\Client\BrowserInterface;

/**
 * Assert products are/aren't accessible by direct link on the storefront.
 */
class AssertProductAccessibleByDirectLink extends AbstractConstraint
{
    /**
     * Message on the product page 404.
     */
    const NOT_FOUND_MESSAGE = 'Whoops, our bad...';

    /**
     * Assert products are/aren't accessible by direct link on the storefront.
     *
     * @param CmsIndex $cmsIndex
     * @param CatalogProductView $productView
     * @param BrowserInterface $browser
     * @param array $productsPresentInCatalog
     * @param array $productsAbsentInCatalog
     * @return void
     */
    public function processAssert(
        CmsIndex $cmsIndex,
        CatalogProductView $productView,
        BrowserInterface $browser,
        array $productsPresentInCatalog,
        array $productsAbsentInCatalog
    ) {
        $cmsIndex->open();
        foreach ($productsPresentInCatalog as $product) {
            $browser->open($_ENV['app_frontend_url'] . $product->getUrlKey() . '.html');
            \PHPUnit\Framework\Assert::assertNotEquals(
                self::NOT_FOUND_MESSAGE,
                $productView->getTitleBlock()->getTitle(),
                'Product \'' . $product->getName() . '\' is not accessible by direct link.'
            );
        }

        foreach ($productsAbsentInCatalog as $product) {
            $browser->open($_ENV['app_frontend_url'] . $product->getUrlKey() . '.html');
            \PHPUnit\Framework\Assert::assertEquals(
                self::NOT_FOUND_MESSAGE,
                $productView->getTitleBlock()->getTitle(),
                'Product \'' . $product->getName() . '\' is accessible by direct link.'
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
        return 'Products are/aren\'t accessible by direct link on the storefront.';
    }
}
