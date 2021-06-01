<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace ForeverCompanies\Utf8\Helper\Product;

use Magento\Framework\View\Result\Page as ResultPage;

/**
 * Catalog category helper
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class ProductView extends \Magento\Catalog\Helper\Product\View
{
    
    
    /**
     * Prepares product view page - inits layout and all needed stuff
     *
     * $params can have all values as $params in \Magento\Catalog\Helper\Product - initProduct().
     * Plus following keys:
     *   - 'buy_request' - \Magento\Framework\DataObject holding buyRequest to configure product
     *   - 'specify_options' - boolean, whether to show 'Specify options' message
     *   - 'configure_mode' - boolean, whether we're in Configure-mode to edit product configuration
     *
     * @param \Magento\Framework\View\Result\Page $resultPage
     * @param int $productId
     * @param \Magento\Framework\App\Action\Action $controller
     * @param null|\Magento\Framework\DataObject $params
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @return \Magento\Catalog\Helper\Product\View
     */
    public function prepareAndRender(ResultPage $resultPage, $productId, $controller, $params = null)
    {
        /**
         * Remove default action handle from layout update to avoid its usage during processing of another action,
         * It is possible that forwarding to another action occurs, e.g. to 'noroute'.
         * Default action handle is restored just before the end of current method.
         */
        $defaultActionHandle = $resultPage->getDefaultLayoutHandle();
        $handles = $resultPage->getLayout()->getUpdate()->getHandles();
        if (in_array($defaultActionHandle, $handles)) {
            $resultPage->getLayout()->getUpdate()->removeHandle($resultPage->getDefaultLayoutHandle());
        }
        
        if (!$controller instanceof \Magento\Catalog\Controller\Product\View\ViewInterface) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Bad controller interface for showing product')
                );
        }
        // Prepare data
        $productHelper = $this->_catalogProduct;
        if (!$params) {
            $params = new \Magento\Framework\DataObject();
        }
        
        // Standard algorithm to prepare and render product view page
        $product = $productHelper->initProduct($productId, $controller, $params);
        if (!$product) {
            throw new \Magento\Framework\Exception\NoSuchEntityException(__('Product is not loaded'));
        }
        
        $buyRequest = $params->getBuyRequest();
        if ($buyRequest) {
            $productHelper->prepareProductOptions($product, $buyRequest);
        }
        
        if ($params->hasConfigureMode()) {
            $product->setConfigureMode($params->getConfigureMode());
        }
        
        $this->_eventManager->dispatch('catalog_controller_product_view', ['product' => $product]);
        
        $this->_catalogSession->setLastViewedProductId($product->getId());
        
        if (in_array($defaultActionHandle, $handles)) {
            $resultPage->addDefaultHandle();
        }
        
        $this->initProductLayout($resultPage, $product, $params);
        $this->preparePageMetadata($resultPage, $product);
        return $this;
    }
    
    
    /**
     * Add meta information from product to layout
     *
     * @param \Magento\Framework\View\Result\Page $resultPage
     * @param \Magento\Catalog\Model\Product $product
     * @return $this
     */
    private function preparePageMetadata(ResultPage $resultPage, $product)
    {
        $pageLayout = $resultPage->getLayout();
        $pageConfig = $resultPage->getConfig();
        
        $metaTitle = $product->getMetaTitle();
        $pageConfig->setMetaTitle($metaTitle);
        $pageConfig->getTitle()->set($metaTitle ?: $product->getName());
        
        $keyword = $product->getMetaKeyword();
        $currentCategory = $this->_coreRegistry->registry('current_category');
        if ($keyword) {
            $pageConfig->setKeywords($keyword);
        } elseif ($currentCategory) {
            $pageConfig->setKeywords($product->getName());
        }
        
        $description = mb_convert_encoding($product->getMetaDescription(), 'Windows-1252', 'UTF-8');
        if ($description) {
            $pageConfig->setDescription($description);
        } else {
            $pageConfig->setDescription($this->string->substr(strip_tags($product->getDescription()), 0, 255));
        }
        
        if ($this->_catalogProduct->canUseCanonicalTag()) {
            $pageConfig->addRemotePageAsset(
                $product->getUrlModel()->getUrl($product, ['_ignore_category' => true]),
                'canonical',
                ['attributes' => ['rel' => 'canonical']]
                );
        }
        
        $pageMainTitle = $pageLayout->getBlock('page.main.title');
        if ($pageMainTitle) {
            $pageMainTitle->setPageTitle($product->getName());
        }
        
        return $this;
    }
    
    
    
}