<?php

namespace ForeverCompanies\CustomSales\Controller\Adminhtml\Choose;

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

class Index extends AdminOrder implements HttpPostActionInterface
{
    /**
     * @inheritDoc
     */
    public function execute()
    {
        exit;
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath('forevercompanies_custom/order/grid');
        return $resultRedirect;
    }

    public function chooseAction()
    {
        exit;
        $test = 1;
    }

    public function choose()
    {
        exit;
        $test = 1;
    }

}
