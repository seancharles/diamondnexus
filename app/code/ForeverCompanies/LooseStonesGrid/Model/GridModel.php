<?php

namespace ForeverCompanies\LooseStonesGrid\Model;

use Magento\Eav\Model\Config;

class GridModel
{
    protected $eavConfig;
    
    public function __construct(
        Config $config
    ) {
        $this->eavConfig = $config;
    }
    
    public function getOptions($code)
    {
        $attribute = $this->eavConfig->getAttribute('catalog_product', $code);
        $options = $attribute->getSource()->getAllOptions();
        $res = [];
        foreach($options as $opt) {
            $res[$opt['value']]=$opt['label'];
        }
        
        return $res;
    }
}