<?php
namespace DiamondNexus\Multipay\Controller\Order;

use Magento\Backend\App\Action\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\Message\ManagerInterface;

class Paynow implements \Magento\Framework\App\Action\HttpGetActionInterface
{
    /**
     * @var PageFactory
     */
    protected $pageFactory;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var ResponseInterface
     */
    protected $response;

    /**
     * @var RedirectFactory
     */
    protected $resultRedirectFactory;

    /**
     * @var ResultFactory
     */
    protected $resultFactory;

    /**
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;
    
    /**
     * @var Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var ManagerInterface
     */
    protected $messageManager;

    /**
     * Paynow constructor.
     * @param Context $context
     * @param PageFactory $pageFactory
     */
    public function __construct(
        Context $context,
        PageFactory $pageFactory,
        OrderRepositoryInterface $orderRepository,
        Session $customerSession,
        ManagerInterface $messageManager
    ) {
        $this->pageFactory = $pageFactory;
        $this->request = $context->getRequest();
        $this->response = $context->getResponse();
        $this->resultRedirectFactory = $context->getResultRedirectFactory();
        $this->resultFactory = $context->getResultFactory();
        $this->orderRepository = $orderRepository;
        $this->customerSession = $customerSession;
        $this->messageManager = $messageManager;
    }

    /**
     * @return ResponseInterface|ResultInterface|Page
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('order_id');
        $openForm = $this->getRequest()->getParam('openform');
        
        /** @var Order $order */
        $order = $this->orderRepository->get($id);

        $totalDue = $order->getGrandTotal() - $order->getTotalPaid();

        $resultRedirect = $this->resultRedirectFactory->create();

        $customerId = $this->customerSession->getCustomer()->getId();

        if($customerId > 0 && $order->getCustomerId() == $customerId) {
            if($order->getTotalPaid() >= $order->getGrandTotal()) {
                $this->messageManager->addError(__("Order is already paid in full."));
                
                return $resultRedirect->setPath('sales/order/history');
            }
            
            $page = $this->pageFactory->create();
            /** @var \DiamondNexus\Multipay\Block\Order\Paynow $block */
            $block = $page->getLayout()->getBlock('diamondnexus_paynow');
            $block->setData('order_id', $id);
            $block->setData('total_due', $totalDue);
            $block->setData('open_form', $openForm);
            return $page;
        } else {
            // order is invalid or the customer does not own it
            return $resultRedirect->setPath('customer/account/login/');
        }
    }

    /**
     * Retrieve request object
     *
     * @return RequestInterface
     */
    public function getRequest()
    {
        return $this->request;
    }
}
