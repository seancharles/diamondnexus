<?php
namespace ForeverCompanies\LooseStoneImport\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use ForeverCompanies\LooseStoneImport\Model\StoneImport;

class Index extends Action
{
    protected $stoneModel;
    

	public function __construct(
		Context $context,
	    StoneImport $stone
	) {
		$this->stoneModel = $stone;
		
		return parent::__construct($context);
	}
	
	public function execute()
	{
	    $this->stoneModel->run();
	    return;
	}
}