<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace ForeverCompanies\CustomApi\Model;

use ForeverCompanies\CustomApi\Api\ReindexInterface;
use Magento\Catalog\Model\Indexer\Category\Product;
use Magento\Catalog\Model\Indexer\Product\Category;
use Magento\Catalog\Model\ProductRepository;
use Magento\Framework\Exception\NoSuchEntityException;

class Reindex implements ReindexInterface
{
    /**
     * @var Category
     */
    protected $categoryIndex;

    /**
     * @var Product
     */
    protected $productIndex;

    /**
     * @var ProductRepository
     */
    protected $productRepository;

    /**
     * Reindex constructor.
     * @param Product $productIndex
     * @param Category $categoryIndex
     * @param ProductRepository $productRepository
     */
    public function __construct(
        Product $productIndex,
        Category $categoryIndex,
        ProductRepository $productRepository
    )
    {
        $this->productIndex = $productIndex;
        $this->categoryIndex = $categoryIndex;
        $this->productRepository = $productRepository;
    }

    /**
     * @param $productIds
     * @return string
     * @throws NoSuchEntityException
     */
    public function reindexProducts($productIds)
    {
        $ids = [];
        foreach ($productIds as $productId) {
            $product = $this->productRepository->getById($productId);
            $ids = array_merge($ids, $product->getData('category_ids'));
        }
        $this->productIndex->execute($ids);
        return 'Reindex success';
    }

    /**
     * @param $categoryIds
     * @return string
     */
    public function reindexCategories($categoryIds)
    {
        $this->categoryIndex->execute($categoryIds);;
        return 'Reindex success';
    }
}
