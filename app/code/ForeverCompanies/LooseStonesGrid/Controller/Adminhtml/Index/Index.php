<?php

namespace ForeverCompanies\LooseStonesGrid\Controller\Adminhtml\Index;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;

class Index extends Action implements HttpGetActionInterface
{
    const MENU_ID = 'ForeverCompanies_LooseStonesGrid::home';
    
    protected $resultPageFactory;
    
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory
        ) {
            parent::__construct($context);
            
            $this->resultPageFactory = $resultPageFactory;
    }
    
    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu(static::MENU_ID);
        $resultPage->getConfig()->getTitle()->prepend(__('Loose Stones'));
        
        return $resultPage;
    }
}