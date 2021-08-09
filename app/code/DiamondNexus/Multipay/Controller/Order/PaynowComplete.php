<?php

namespace DiamondNexus\Multipay\Controller\Order;

use DiamondNexus\Multipay\Block\Order\Paynow;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
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
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\CsrfAwareActionInterface;

use DiamondNexus\Multipay\Model\Constant;

class PaynowComplete extends Action implements CsrfAwareActionInterface
{
    protected $pageFactory;
    protected $request;
    protected $response;
    protected $resultRedirectFactory;
    protected $resultFactory;
    protected $orderRepository;
    protected $customerSession;
    protected $messageManager;
    
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
        
        parent::__construct($context);
    }

    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();

        if ($this->customerSession->isLoggedIn() === true) {
            $id = (int) $this->getRequest()->getParam('order_id');
            $openForm = (bool) $this->getRequest()->getParam('openform');

            /** @var Order $order */
            $order = $this->orderRepository->get($id);

            $customerId = $this->customerSession->getCustomer()->getId();

            if ($customerId > 0 && $order->getCustomerId() == $customerId) {
                if (round($order->getTotalPaid(), 2) < round($order->getGrandTotal(), 2)) {
                    $this->messageManager->addError(__("Order is not paid in full."));

                    return $resultRedirect->setPath('sales/order/history');
                }

                $page = $this->pageFactory->create();
                /** @var Paynow $block */
                $block = $page->getLayout()->getBlock('diamondnexus_paynow');
                $block->setData('order_id', $order->getIncrementId());
                return $page;
            } else {
                return $resultRedirect->setPath('customer/account/login/');
            }
        } else {
            return $resultRedirect->setPath('customer/account/login/');
        }
    }
    
    public function createCsrfValidationException(RequestInterface $request): ?       InvalidRequestException
    {
        return null;
    }
    
    public function validateForCsrf(RequestInterface $request): ?bool
    {
        return true;
    }
}
