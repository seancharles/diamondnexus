<?php

namespace ForeverCompanies\CustomSales\Controller\Adminhtml\Order;

use Magento\Backend\App\Action;
use Magento\Backend\Model\Session as AdminSession;

class Choose extends Action
{
    protected $session;
    
    public function __construct(AdminSession $adminS)
    {
        $this->session = $adminS;
    }
    
    public function execute()
    {
        $this->session->setSalesPersonId($this->getRequest()->getParam('id'));
        $this->session->setStatus($this->getRequest()->getParam('status'));
        $resultRedirect->setPath('sales/order_create/index');
        return $resultRedirect;
    }
}
