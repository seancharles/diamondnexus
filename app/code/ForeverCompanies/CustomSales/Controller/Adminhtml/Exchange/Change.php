<?php

namespace ForeverCompanies\CustomSales\Controller\Adminhtml\Exchange;

use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\Request\Http;
use Magento\Sales\Controller\Adminhtml\Order as AdminOrder;

class Change extends AdminOrder implements HttpPostActionInterface
{
    /**
     * @inheritDoc
     */
    public function execute()
    {
        /** @var Http $request */
        $request = $this->getRequest();
        $postParams = $request->getPostValue();
        $orderId = $postParams['order_id'];
        $isExchange = '0';
        if (isset($postParams['is_exchange'])) {
            $isExchange = $postParams['is_exchange'];
        }
        $order = $this->orderRepository->get($orderId);
        if ($order->getData('is_exchange') !== $isExchange) {
            $order->setData('is_exchange', $isExchange);
            $this->orderRepository->save($order);
        }

        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath('sales/order/view', ['order_id' => $orderId]);
        return $resultRedirect;
    }
}
