<?php

//    require_once $_SERVER['HOME'] . 'magento//Mage.php';
    
    Mage::app();
    
    // how far back to start removing quotes
    $lifetime = (86400 * 90);
        
    $quotes = Mage::getModel('sales/quote')->getCollection();
    /* @var $quotes Mage_Sales_Model_Mysql4_Quote_Collection */
    
    $quotes->addFieldToFilter('updated_at', array('to'=>date("Y-m-d", time()-$lifetime)));
    // not converted
    $quotes->setOrder('updated_at');
    $quotes->walk('delete');
    