<?php

namespace ForeverCompanies\CustomSales\Controller\Adminhtml\Exchange;

use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\Request\Http;
use Magento\Sales\Controller\Adminhtml\Order as AdminOrder;

class Start extends AdminOrder implements HttpPostActionInterface
{
    /**
     * @inheritDoc
     */
    public function execute()
    {
        /** @var Http $request */
        $request = $this->getRequest();
        $params = [];
        $postParams = $request->getPostValue();
        if (isset($postParams['is_exchange'])) {
            $params['is_exchange'] = $postParams['is_exchange'];
        }
        $refererUrl = $this->_redirect->getRefererUrl();
        $statusString = stristr($refererUrl, 'status/');
        $statusString = str_replace('status/', '', $statusString);
        if ($statusString !== '') {
            $position = strpos($statusString, "/");
            $status = substr($statusString, 0, $position);
            $params['status'] = $status;
        }
        /*if ($this->getRequest()->getPostValue()['is_exchange'] !== null) {
            $params['is_exchange'] = $this->getRequest()->getParam('is_exchange');
            $refererUrl = $this->_redirect->getRefererUrl();
            $exchangeString = stristr($refererUrl, 'is_exchange/');
            $exchangeString = str_replace('is_exchange/', '', $exchangeString);
            if ($exchangeString == '') {
                $params['is_exchange'] = '1';
            }
            if ($exchangeString == '1') {
                $params['is_exchange'] = '0';
            }
        }*/
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath('sales/order_create/index', $params);
        return $resultRedirect;
    }
}
