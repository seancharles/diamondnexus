<?php

namespace ForeverCompanies\Profile\Model;

use Magento\Catalog\Model\ProductRepository as OrigProductRepository;
use Magento\Catalog\Api\ProductRepositoryInterface;

class ProductRepository extends OrigProductRepository implements ProductRepositoryInterface
{
    public function get($sku, $editMode = false, $storeId = null, $forceReload = false)
    {
        $cacheKey = $this->getCacheKey([$editMode, $storeId]);
        
        if (trim($sku) == "") {
            return null;
        }
        
        if ($cachedProduct === null || $forceReload) {
            $product = $this->productFactory->create();
            
            $productId = $this->resourceModel->getIdBySku($sku);
            if (!$productId) {
                throw new NoSuchEntityException(
                    __("The product that was requested doesn't exist. Verify the product and try again.")
                    );
            }
            if ($editMode) {
                $product->setData('_edit_mode', true);
            }
            if ($storeId !== null) {
                $product->setData('store_id', $storeId);
            }
            $product->load($productId);
            $this->cacheProduct($cacheKey, $product);
            $cachedProduct = $product;
        }
        
        return $cachedProduct;
    }
}
