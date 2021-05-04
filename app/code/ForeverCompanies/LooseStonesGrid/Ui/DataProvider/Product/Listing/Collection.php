<?php
namespace ForeverCompanies\LooseStonesGrid\Ui\DataProvider\Product\Listing;

use Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult;

class Collection extends SearchResult
{
    /**
     * Override _initSelect to add custom columns
     *
     * @return void
     */
    protected function _initSelect()
    {
        $this->addFilterToMap('entity_id', 'main_table.entity_id');
      
        $this->addFilterToMap('blockchain_verified', 'main_table.blockchain_verified');
        
        
        
        
        
        $this->addFieldToFilter("attribute_set_id", array("eq" => 31));
        parent::_initSelect();
    }
}