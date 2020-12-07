<?php

namespace ForeverCompanies\CustomSales\Controller\Adminhtml\Order;

use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Sales\Controller\Adminhtml\Order as AdminOrder;

class User extends AdminOrder implements HttpPostActionInterface
{
    /**
     * @inheritDoc
     */
    public function execute()
    {
        $params = [];
        if ($this->getRequest()->getParam('status') !== null) {
            $params['status'] = $this->getRequest()->getParam('status');
        }
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath('forevercompanies_custom/order/grid', $params);
        return $resultRedirect;
    }
}
