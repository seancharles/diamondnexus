<?php

namespace ForeverCompanies\CustomSales\Controller\Adminhtml\Order;

use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Sales\Controller\Adminhtml\Order as AdminOrder;

class Edit extends AdminOrder implements HttpPostActionInterface
{
    /**
     * @inheritDoc
     */
    public function execute()
    {
        $orderId = $this->getRequest()->getParam('order_id');
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath('forevercompanies_custom/order/change', ['order_id' => $orderId]);
        return $resultRedirect;
    }
}
