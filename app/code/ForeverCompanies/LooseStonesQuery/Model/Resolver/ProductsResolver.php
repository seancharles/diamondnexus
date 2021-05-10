<?php
declare(strict_types=1);

namespace ForeverCompanies\LooseStonesQuery\Model\Resolver;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

/**
 * Product collection resolver
 */
class ProductsResolver implements ResolverInterface
{
    public function __construct(
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
        ) {
            $this->productRepository = $productRepository;
            $this->searchCriteriaBuilder = $searchCriteriaBuilder;
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
        
            echo '<pre>';
            var_dump("args", $args);
            die;
        
            $productsData = $this->getProductsData();
            return $productsData;
    }
    
    /**
     * @return array
     * @throws GraphQlNoSuchEntityException
     */
    private function getProductsData(): array
    {
        
        echo '<pre>';
        var_dump("this keys", array_keys($this->getData()));
        die;
        try {
            /* filter for all the pages */
            $searchCriteria = $this->searchCriteriaBuilder->addFilter('product_type', '3569','eq')->create();
            
            
            
            $products = $this->productRepository->getList($searchCriteria)->getItems();
            echo 'the product count is ' . count($products);die;
            
            $productRecord['allProducts'] = [];
           // $productId = $product->getId();
            foreach($products as $product) {
                
                echo '<pre>';
                var_dump("kes", array_keys($product->getData()));
                die;
                
                
                $productRecord['allProducts'][$productId]['sku'] = $product->getSku();
                $productRecord['allProducts'][$productId]['name'] = $product->getName();
                $productRecord['allProducts'][$productId]['price'] = $product->getPrice();
            }
        } catch (NoSuchEntityException $e) {
            throw new GraphQlNoSuchEntityException(__($e->getMessage()), $e);
        }
        return $productRecord;
    }
}