<?php

namespace ForeverCompanies\DynamicBundle\Plugins;

class BundleConfigurationHelper
{

    public function afterGetBundleOptions($item, $result)
    {
        //$product = $item->getProduct();
        
        /*
        if ($product->get)
        {

        }
        */
        
        //mail('paul.baum@forevercompanies.com','afterGetBundleOptions',print_r($item,true));
        mail('paul.baum@forevercompanies.com', 'afterGetBundleOptions', print_r(get_class_methods($item), true));
        
        return $result;
    }
    
    public function afterGetSelectionFinalPrice($item, $result)
    {
        //$product = $item->getProduct();
        
        //mail('paul.baum@forevercompanies.com','afterGetBundleOptions',print_r(get_class_methods($item), true));
        
        return 100.00;
    }
}
