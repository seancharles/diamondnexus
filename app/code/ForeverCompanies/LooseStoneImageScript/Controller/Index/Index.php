<?php
namespace ForeverCompanies\LooseStoneImageScript\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use ForeverCompanies\LooseStoneImport\Model\StoneImport;

class Index extends Action
{   
    protected $scopeConfig;
    protected $storeScope;
    
    protected $stoneImportModel;
    
	public function __construct(
		Context $context,
	    ScopeConfigInterface $scopeC,
	    StoneImport $stoneI
	) {
		$this->scopeConfig = $scopeC;
		$this->storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
		$this->stoneImportModel = $stoneI;
		
		return parent::__construct($context);
	}
	
	public function execute()
	{
	    $this->stoneImportModel->updateCsv();
	    $csvArray = $this->stoneImportModel->buildArray();
	    
	    foreach ($csvArray as $csvArr) {
	        $productId = $this->productModel->getIdBySku($csvArr['Certificate #']);
	        if ($productId) {
	            $product = $this->productModel->load($productId); 
	        }
	    }
	    
	    
	    return;
	}
}