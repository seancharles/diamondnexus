<?php
namespace ForeverCompanies\StoneEmail\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use ForeverCompanies\StoneEmail\Model\StoneEmail;

class Index extends Action
{
    protected $stoneModel;
    

	public function __construct(
		Context $context,
	    StoneEmail $stone
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