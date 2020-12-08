<?php

namespace ForeverCompanies\CustomSales\Controller\Adminhtml\Order;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Change extends \Magento\Backend\App\Action
{

    /**
     * @var bool|PageFactory
     */
    protected $resultPageFactory = false;

    /**
     * @var Context
     */
    private $context;

    /**
     * Index constructor.
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        PageFactory $resultPageFactory,
        Context $context
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->context = $context;
    }

    /**
     * @return mixed
     */
    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->prepend(__('Choose admin user for change sales person'));
        return $resultPage;
    }
}
