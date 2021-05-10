<?php

declare(strict_types=1);

namespace ForeverCompanies\LooseStonesQuery\Model\Resolver\Products\SearchCriteria\CollectionProcessor\FilterProcessor;

use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Framework\Api\SearchCriteria\CollectionProcessor\FilterProcessor\CustomFilterInterface;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Api\Filter;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable;
use Magento\Framework\Registry;
use Magento\Framework\App\RequestInterface;

/**
 * Category filter allows to filter products collection using custom defined filters from search criteria.
 */
class ProductAttributeFilter implements CustomFilterInterface
{
    protected $configurable;
    protected $collectionFactory;
    protected $registry;
    
    public function __construct(
        Configurable $configurable,
        CollectionFactory $collectionFactory,
        \Psr\Log\LoggerInterface $logger,
        Registry $registry,
        RequestInterface $request
        ) {
            $this->registry = $registry;
            $this->configurable = $configurable;
            $this->logger = $logger;
            $this->collectionFactory = $collectionFactory;
            
            /*
            $ret = array();
            $ret['data'] = array();
            $ret['data']['products'] = array();
            
            echo json_encode($ret);die;
            */
            
            
    }
    
    public function resolve(ContextInterface $context, Field $field, array $requests): BatchResponse
    {
        echo 'fff';die;
        //Get the list of products we need to load related products for
        $rootProductIds = array_map(function ($request) { return $request->getValue()['model']->getId(); }, $requests);
        
        //Load the links
        $productLinks = $this->service->getRelatedProductLinks($rootProductIds);
        
        //Sort the links
        $response = new BatchResponse();
        foreach ($requests as $request) {
            $response->addResponse($request, $productLinks[$request->getValue()['model']->getId()]);
        }
        
        return $response;
    }
    
    public function apply(Filter $filter, AbstractDb $collection)
    {   
        echo 'asdf';die;
        $conditionType = $filter->getConditionType();
        $attributeName = $filter->getField();
        $attributeValue = $filter->getValue();
        $category = $this->registry->registry('current_category');
        
        
        if($attributeName == 'language'){
            $conditions = [];
            foreach ($attributeValue as $value){
                $conditions[] = ['attribute'=>$attributeName, 'finset'=>$value];
            }
            $simpleSelect = $this->collectionFactory->create()
            ->addAttributeToFilter($conditions);
            
        }else{
            $simpleSelect = $this->collectionFactory->create()
            ->addAttributeToFilter($attributeName, [$conditionType => $attributeValue]);
        }
        
        
        $simpleSelect->addAttributeToFilter('status', Status::STATUS_ENABLED);
        /*
        if ($category) {
            $simpleSelect->addCategoriesFilter(['in' => (int)$category->getId()]);
        }
        */
        
        
        $arr =  $simpleSelect;
        $entity_ids = [];
        foreach ($arr->getData() as $a){
            $entity_ids[] = $a['entity_id'];
        }
        
        $collection->getSelect()->where($collection->getConnection()->prepareSqlCondition(
            'e.entity_id', ['in' => $entity_ids]
            ));
        
        return true;
    }
}
