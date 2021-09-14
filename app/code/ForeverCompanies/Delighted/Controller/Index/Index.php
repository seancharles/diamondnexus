<?php
namespace ForeverCompanies\Delighted\Controller\Index;

use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\Action;
use Magento\Sales\Model\OrderFactory;

use ForeverCompanies\Delighted\Model\Person;
use Magento\Directory\Model\RegionFactory;

class Index extends Action
{
    protected $connection;
    protected $messageManager;
    protected $productFactory;
    
    protected $orderFactory;
    
    protected $person;
    protected $regionFactory;
    
	public function __construct(
		Context $context,
	    Person $person,
	    OrderFactory $orderF,
	    RegionFactory $regionF
	) {
	    $this->person = $person;
	    $this->orderFactory = $orderF;
	    $this->regionFactory = $regionF;
	    
		return parent::__construct($context);
	}

	public function execute()
	{
	    echo 'fff';die;
	    $order = $this->orderFactory->create()->load(557895);
	    
	    
	    $region = $this->regionFactory->create()->load( $order->getBillingAddress()->getRegionId() );
	    
	 
	    
	    $result = $this->person->create([
	        'email' => $order->getCustomerEmail(),
	        'properties' => [
	            'Purchase Experience' => $order->getStore()->getName(),
	            'State' => $region->getCode()
	        ]
	    ]);
	    
	    echo '<pre>';
	    var_dump("result", $result);
	    
	    echo 'delighted action';
	    
	    die;
	}
	
}