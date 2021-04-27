<?php

namespace ForeverCompanies\Checkout\Plugins\Helper;

class Cart
{
    public function afterGetItemsCount($subject, $result)
    {
        //return $this->getCart()->getItemsCount();
        
        // rewrite to return set items count correctly
        
        return $subject;
    }
}
