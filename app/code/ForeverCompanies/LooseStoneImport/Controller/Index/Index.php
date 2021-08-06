<?php
namespace ForeverCompanies\LooseStoneImport\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use ForeverCompanies\LooseStoneImport\Model\StoneImport;
use Magento\Framework\App\Config\ScopeConfigInterface;

class Index extends Action
{
    protected $stoneModel;
    
    protected $scopeConfig;
    protected $storeScope;
    
	public function __construct(
		Context $context,
	    StoneImport $stone,
	    ScopeConfigInterface $scopeC
	) {
		$this->stoneModel = $stone;
		
		$this->scopeConfig = $scopeC;
		$this->storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
		
		return parent::__construct($context);
	}
	
	public function execute()
	{
	    echo 'Comment out at app/code/ForeverCompanies/LooseStoneImport/Controller/Index/Index.php';die;
	    $this->stoneModel->run();
	    return;
	}
}