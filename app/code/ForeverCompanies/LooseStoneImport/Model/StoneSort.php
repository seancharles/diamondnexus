<?php

namespace ForeverCompanies\LooseStoneImport\Model;

use Magento\Catalog\Model\Product\Action as ProductAction;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;

class StoneSort
{    
    private $productCollection;
    private $productAction;
    
    public function __construct(
        CollectionFactory $collection,
        ProductAction $action
    ) {
        $this->productCollection = $collection;
        $this->productAction = $action;
    }
    
    function run()
    {
        try {
            $collection = $this->productCollection->create()
            ->addAttributeToFilter('fc_product_type', array('eq' => '3569'))
            ->addAttributeToFilter('shape', array('eq' => '2842'))
            ->load();
            
            $ids = [];
            $i = 0;
            foreach ($collection as $item) {
                $ids[$i] = $item->getEntityId();
                $i++;
            }
            $this->productAction->updateAttributes($ids, array('shape_alpha_sort' => '950'), 12);
            
            $collection = $this->productCollection->create()
            ->addAttributeToFilter('fc_product_type', array('eq' => '3569'))
            ->addAttributeToFilter('shape', array('eq' => '2846'))
            ->load();
            
            $ids = [];
            $i = 0;
            foreach ($collection as $item) {
                $ids[$i] = $item->getEntityId();
                $i++;
            }
            $this->productAction->updateAttributes($ids, array('shape_pop_sort' => '950'), 12);
        } catch (\Exception $e) {
            
        }
    }
}
