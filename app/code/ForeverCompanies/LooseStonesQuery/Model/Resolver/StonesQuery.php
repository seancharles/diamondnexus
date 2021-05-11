<?php
declare(strict_types=1);

namespace ForeverCompanies\LooseStonesQuery\Model\Resolver;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\GraphQl\Query\Resolver\BatchResolverInterface;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;



class StonesQuery implements ResolverInterface
{
    protected $productColl;
    
    
    public function __construct(
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        CollectionFactory $collectionFac
        ) {
        
            $this->productRepository = $productRepository;
            $this->searchCriteriaBuilder = $searchCriteriaBuilder;
            $this->productColl = $collectionFac->create();
            
    }
    
    
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
        ) { 
            
            $onlineEqFilter = $args['filter']['online']['eq'];
            $productTypeEqFilter = $args['filter']['product_type']['eq'];
            $colorInFilter = $args['filter']['color']['in'];
            $clarityInFilter = $args['filter']['clarity']['in'];
            $caratWeightFromFilter = $args['filter']['carat_weight']['from'];
            $caratWeightToFilter = $args['filter']['carat_weight']['to'];
            $priceFromFilter = $args['filter']['price']['from'];
            $priceToFilter = $args['filter']['price']['to'];
            $cutGradeInFilter = $args['filter']['cut_grade']['in'];
            $shapeInFilter = $args['filter']['shape']['in'];
            $pageSize = $args['pageSize'];
            $currentPage = $args['currentPage'];
            
            foreach ($args['sort'] as $k => $v) {
                $sortKey = $k;
                $sortVal = $v;
            }
            
            $this->productColl->addAttributeToFilter('online', $onlineEqFilter);
            $this->productColl->addAttributeToFilter('product_type', $productTypeEqFilter);
            $this->productColl->addAttributeToFilter('color', array('in' => $colorInFilter));
            $this->productColl->addAttributeToFilter('clarity', array('in' =>  $clarityInFilter));
            $this->productColl->addAttributeToFilter('cut_grade', array('in' =>  $cutGradeInFilter));
            $this->productColl->addAttributeToFilter('shape', array('in' =>  $shapeInFilter));
            
            $this->productColl->addAttributeToFilter('carat_weight', array('gteq' =>  $caratWeightFromFilter));
            $this->productColl->addAttributeToFilter('carat_weight', array('lteq' =>  $caratWeightToFilter));
            $this->productColl->addAttributeToFilter('price', array('gteq' =>  $priceFromFilter));
            $this->productColl->addAttributeToFilter('price', array('lteq' =>  $priceToFilter));
         
            $this->productColl->setPageSize($pageSize);
            $this->productColl->setCurPage($currentPage);
            
            $this->productColl->addAttributeToSelect("*")->load();
            
            echo 'the product coll count is ' . count($this->productColl);
            
            foreach ($this->productColl as $_product) {
                echo $_product->getName() . "    ";
            }
            
            die;
            
            var_dump($sortKey);
            var_dump($sortVal);
            die;
            
            
            foreach ($args['filter'] as $argFilter) {
                echo '<pre>';
                var_dump($argFilter);
            }
            die;
            echo '<pre>';
            var_dump($args['filter']['online']['eq']);
            die;
            echo $args['filter']['online'];die;
            
            echo '<pre>';
            var_dump("args", $args);
            die;
            
        
            $productsData = $this->getProductsData();
            return $productsData;
    }
    

    
    private function getProductsData(): array
    {
        
        try {
            
            $searchCriteria = $this->searchCriteriaBuilder->addFilter('sku', 'LG_M3D44067', 'eq')->create();
            
            
            
            $products = $this->productRepository->getList($searchCriteria)->getItems();
            // echo 'the product count is ' . count($products);die;
            
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