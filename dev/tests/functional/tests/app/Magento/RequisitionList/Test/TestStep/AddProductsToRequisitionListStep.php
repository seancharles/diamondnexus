<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\RequisitionList\Test\TestStep;

use Magento\Catalog\Test\Page\Product\CatalogProductView;
use Magento\Mtf\Client\BrowserInterface;
use Magento\Mtf\TestStep\TestStepInterface;

/**
 * Class AddProductsToRequisitionListStep
 * Adding created products to the requisition list
 */
class AddProductsToRequisitionListStep implements TestStepInterface
{
    /**
     * Array with products
     *
     * @var array
     */
    protected $products;

    /**
     * Storefront product view page
     *
     * @var CatalogProductView
     */
    protected $catalogProductView;

    /**
     * Interface Browser
     *
     * @var BrowserInterface
     */
    protected $browser;

    /**
     * Requisition list name
     *
     * @var string
     */
    protected $requisitionListName;

    /**
     * @constructor
     * @param CatalogProductView $catalogProductView
     * @param BrowserInterface $browser
     * @param array $products
     * @param string $requisitionListName
     */
    public function __construct(
        CatalogProductView $catalogProductView,
        BrowserInterface $browser,
        array $products,
        $requisitionListName
    ) {
        $this->products = $products;
        $this->catalogProductView = $catalogProductView;
        $this->browser = $browser;
        $this->requisitionListName = $requisitionListName;
    }

    /**
     * Add products to the requisition list
     *
     * @return void
     */
    public function run()
    {
        foreach ($this->products as $product) {
            $this->browser->open($_ENV['app_frontend_url'] . $product->getUrlKey() . '.html');
            $this->catalogProductView->getViewBlock()->fillOptions($product);
            $checkoutData = $product->getCheckoutData();
            if (isset($checkoutData['qty'])) {
                $qty = $checkoutData['qty'];
                $this->catalogProductView->getViewBlock()->setQty($qty);
            }
            $this->catalogProductView
                ->getProductSocialLinksBlock()
                ->clickAddToRequisitionList($this->requisitionListName);
        }
    }
}
