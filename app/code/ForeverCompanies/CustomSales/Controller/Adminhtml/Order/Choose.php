<?php

namespace ForeverCompanies\CustomSales\Controller\Adminhtml\Order;

use Magento\Backend\App\Action;

class Choose extends Action
{
    /**
     * @inheritDoc
     */
    public function execute()
    {
        $refererUrl = $this->_redirect->getRefererUrl();
        $salesPersonString = stristr($refererUrl, 'status/');
        $salesPersonString = str_replace('status/', '', $salesPersonString);
        $position = strpos($salesPersonString, "/");
        $status = substr($salesPersonString, 0, $position);
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath('sales/order_create/index', [
            'sales_person_id' => $this->getRequest()->getParam('id'),
            'status' => $status
        ]);
        return $resultRedirect;
    }
}
