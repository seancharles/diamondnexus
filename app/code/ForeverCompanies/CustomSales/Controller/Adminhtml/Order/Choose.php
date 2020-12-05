<?php

namespace ForeverCompanies\CustomSales\Controller\Adminhtml\Order;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Registry;
use Magento\Framework\Translate\InlineInterface;
use Magento\Framework\View\Result\LayoutFactory;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;
use Magento\Sales\Api\OrderManagementInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Controller\Adminhtml\Order as AdminOrder;
use Psr\Log\LoggerInterface;
use ShipperHQ\Shipper\Model\ResourceModel\Order\GridDetail;

class Choose extends \Magento\Backend\App\Action
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
