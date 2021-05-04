<?php

namespace ForeverCompanies\LooseStonesGrid\Plugin;

use Magento\Catalog\Ui\DataProvider\Product\ProductDataProvider as RelatedProductDataProvider;


class ProductDataProvider extends RelatedProductDataProvider
{

    public function afterGetData(
        \Magento\Catalog\Ui\DataProvider\Product\ProductDataProvider $subject,
        $result
        ) {
                
         //   return $result;
            if (1==0 && $subject->getCollection()->isLoaded()) {
                $subject->getCollection()->add('attribute_set_id', array('eq' => '31'));
                $subject->getCollection()->load();
            }
            
            $items = $subject->getCollection()->toArray();
            
            return [
                'totalRecords' => $subject->getCollection()->getSize(),
                'items' => array_values($items),
            ];
    }

}