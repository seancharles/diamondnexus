<?php

namespace DiamondNexus\Multipay\Controller\Adminhtml\Order;

use DiamondNexus\Multipay\Helper\Data;
use DiamondNexus\Multipay\Model\Constant;
use DiamondNexus\Multipay\Model\ResourceModel\Transaction;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\Exception\ValidatorException;
use Magento\Framework\Registry;
use Magento\Framework\Translate\InlineInterface;
use Magento\Framework\View\Result\LayoutFactory;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;
use Magento\Sales\Api\OrderManagementInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Controller\Adminhtml\Order as AdminOrder;
use Magento\Sales\Model\Order as OrderModel;
use Psr\Log\LoggerInterface;

class Order extends AdminOrder implements HttpPostActionInterface
{
    /**
     * Changes ACL Resource Id
     */
    const ADMIN_RESOURCE = 'Magento_Sales::hold';

    /**
     * @var Transaction
     */
    protected $resource;

    /**
     * @var Data
     */
    private $helper;

    /**
     * Order constructor.
     * @param Context $context
     * @param Registry $coreRegistry
     * @param FileFactory $fileFactory
     * @param InlineInterface $translateInline
     * @param PageFactory $resultPageFactory
     * @param JsonFactory $resultJsonFactory
     * @param LayoutFactory $resultLayoutFactory
     * @param RawFactory $resultRawFactory
     * @param OrderManagementInterface $orderManagement
     * @param OrderRepositoryInterface $orderRepository
     * @param LoggerInterface $logger
     * @param Transaction $resource
     * @param Data $helper
     */
    public function __construct(
        Action\Context $context,
        Registry $coreRegistry,
        FileFactory $fileFactory,
        InlineInterface $translateInline,
        PageFactory $resultPageFactory,
        JsonFactory $resultJsonFactory,
        LayoutFactory $resultLayoutFactory,
        RawFactory $resultRawFactory,
        OrderManagementInterface $orderManagement,
        OrderRepositoryInterface $orderRepository,
        LoggerInterface $logger,
        Transaction $resource,
        Data $helper
    ) {
        parent::__construct(
            $context,
            $coreRegistry,
            $fileFactory,
            $translateInline,
            $resultPageFactory,
            $resultJsonFactory,
            $resultLayoutFactory,
            $resultRawFactory,
            $orderManagement,
            $orderRepository,
            $logger
        );
        $this->resource = $resource;
        $this->helper = $helper;
    }

    /**
     * @inheritDoc
     * @throws ValidatorException
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();

        $order = $this->_initOrder();
        if ($order) {
            /** @var Http $request */
            $request = $this->getRequest();
            $post = $request->getPostValue();
            $orderId = $order->getEntityId();
            $info = $post;
            unset($info['form_key']);
            $order->getPayment()->setAdditionalInformation($info);
            $paymentMethod = $post[Constant::PAYMENT_METHOD_DATA];
            if ($paymentMethod == Constant::MULTIPAY_STORE_CREDIT_METHOD) {
                // TODO: add custom logic for amount with store credit
            } elseif ($paymentMethod !== Constant::MULTIPAY_QUOTE_METHOD) {
                // REMOVED FOR PCI COMPLIANCE
                //$this->helper->sendToBraintree($order);
            }
            $this->resource->createNewTransaction($order, $post);
            $this->helper->updateOrderStatus($post, $order);
            $orderArray = ['order_id' => $orderId];
            $resultRedirect->setPath('sales/order/view', $orderArray);
            return $resultRedirect;
        }
        $resultRedirect->setPath('sales/*/');
        return $resultRedirect;
    }
}
