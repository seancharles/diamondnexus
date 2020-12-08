<?php
namespace ForeverCompanies\Salesforce\Controller\Adminhtml\Queue;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Index extends Action
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * Index constructor.
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
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
        $resultPage->setActiveMenu('ForeverCompanies_Salesforce::salesforce');
        $resultPage->addBreadcrumb(__('Queue'), __('Queue'));
        $resultPage->addBreadcrumb(__('Manage Queue'), __('Manage Queue'));
        $resultPage->getConfig()->getTitle()->prepend(__('Queue'));
        return $resultPage;
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('ForeverCompanies_Salesforce::queue');
    }
}
