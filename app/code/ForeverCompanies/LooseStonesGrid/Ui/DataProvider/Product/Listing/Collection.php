<?php
namespace ForeverCompanies\LooseStonesGrid\Ui\DataProvider\Product\Listing;

use Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult;

class Collection extends SearchResult
{
    protected function _initSelect()
    {
        $this->addFilterToMap('entity_id', 'main_table.entity_id');
        $this->addFilterToMap('clarity', 'stonesgrid_clarity.value');
        $this->addFilterToMap('shape', 'stonesgrid_shape.value');
        $this->addFilterToMap('color', 'stonesgrid_color.value');
        $this->addFilterToMap('cut_grade', 'stonesgrid_cut_grade.value');
        $this->addFilterToMap('price', 'stonesgrid_price.value');
        $this->addFilterToMap('carat_weight', 'stonesgrid_carat_weight.value');
        $this->addFilterToMap('supplier', 'stonesgrid_supplier.value');
        
        $this->addFieldToFilter("attribute_set_id", array("eq" => 31));
        
        parent::_initSelect();
    }
}