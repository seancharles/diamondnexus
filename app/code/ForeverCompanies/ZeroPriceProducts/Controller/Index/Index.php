<?php
namespace ForeverCompanies\ZeroPriceProducts\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;

class Index extends Action
{   
    protected $productCollectionFactory;
    
    
	public function __construct(
		Context $context,
	    CollectionFactory $productCollFactory
	) {
	    
	    $this->productCollectionFactory = $productCollFactory;
	    
		return parent::__construct($context);
	}
	
	public function execute()
	{
	    $collection = $this->productCollectionFactory->create();
	    
	    $collection->addAttributeToSelect('sku');
	    $collection->addAttributeToSelect('name');
	    $collection->addAttributeToSelect('price');
	    
	    $collection->addAttributeToFilter('price', array('in' => array("", 0)));
	    
	    $collection->load();
	    
	    
	    foreach ($collection as $product) {
	        echo $product->getSku() . "," . str_replace(",", "", $product->getName()) . "," . $product->getPrice() . '<br />';
	    }
	    
	    die;
	}
}