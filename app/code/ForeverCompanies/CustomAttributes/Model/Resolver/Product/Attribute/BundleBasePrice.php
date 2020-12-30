<?php

declare(strict_types=1);

namespace ForeverCompanies\CustomAttributes\Model\Resolver\Product\Attribute;

use Magento\Catalog\Model\Product;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

/**
 * Resolve data for product Brand name
 */
class BundleBasePrice implements ResolverInterface
{
    /**
     * @var ProductFactory|\Magento\Catalog\Model\ProductFactory
     */
    protected $productFactory;

    /**
     * BundleBasePrice constructor.
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     */
    public function __construct(\Magento\Catalog\Model\ProductFactory $productFactory) {
        $this->productFactory = $productFactory;
    }

    /**
     * @inheritdoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        if (!isset($value['model'])) {
            throw new LocalizedException(__('"model" value should be specified'));
        }

        /* @var $product Product */
        $productModel = $value['model'];

        /**
         * @TODO is there a better way to implement this?
         * $productModel->getDataByKey('price') returns null
         * if we load a new product by id through the product factory though, getDataByKey('price') works
         */

        $bundleBasePrice = null;

        if ($productModel->getId()) {
            $product = $this->productFactory->create()->load($productModel->getId());
            $bundleBasePrice = $product->getDataByKey('price');
        }

        return ($bundleBasePrice) ?: 0.00;
    }
}
