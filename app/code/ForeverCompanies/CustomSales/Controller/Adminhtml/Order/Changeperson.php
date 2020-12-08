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

class Changeperson extends \Magento\Backend\App\Action
{
    /**
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * Changeperson constructor.
     * @param Context $context
     * @param OrderRepositoryInterface $orderRepository
     */
    public function __construct(
        Action\Context $context,
        OrderRepositoryInterface $orderRepository
    ) {
        parent::__construct($context);
        $this->orderRepository = $orderRepository;
    }

    /**
     * @inheritDoc
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $refererUrl = $this->_redirect->getRefererUrl();
        $salesPersonString = stristr($refererUrl, 'order_id/');
        $salesPersonString = str_replace('order_id/', '', $salesPersonString);
        $position = strpos($salesPersonString, "/");
        $orderId = substr($salesPersonString, 0, $position);
        $order = $this->orderRepository->get($orderId);
        $order->setData('sales_person_id', $this->getRequest()->getParam('id'));
        $extension = $order->getExtensionAttributes();
        $extension->setSalesPersonId($this->getRequest()->getParam('id'));
        $order->setExtensionAttributes($extension);
        $this->orderRepository->save($order);
        $resultRedirect->setPath('sales/order/view', [
            'order_id' => $orderId
        ]);
        return $resultRedirect;
    }
}
