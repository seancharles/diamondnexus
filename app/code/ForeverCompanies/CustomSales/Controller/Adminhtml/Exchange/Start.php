<?php

namespace ForeverCompanies\CustomSales\Controller\Adminhtml\Exchange;

use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\Request\Http;
use Magento\Sales\Controller\Adminhtml\Order as AdminOrder;

use Magento\Sales\Api\OrderManagementInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Psr\Log\LoggerInterface;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\Translate\InlineInterface;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\View\Result\LayoutFactory;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Backend\Model\Session\Quote as AdminQuote;
use Magento\Backend\Model\Session as AdminSession;

class Start extends AdminOrder implements HttpPostActionInterface
{
    protected $quote;
    protected $session;
    
    public function __construct(
        Context $context,
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
        AdminQuote $adminQuote,
        AdminSession $adminS
        ) {
            $this->_coreRegistry = $coreRegistry;
            $this->_fileFactory = $fileFactory;
            $this->_translateInline = $translateInline;
            $this->resultPageFactory = $resultPageFactory;
            $this->resultJsonFactory = $resultJsonFactory;
            $this->resultLayoutFactory = $resultLayoutFactory;
            $this->resultRawFactory = $resultRawFactory;
            $this->orderManagement = $orderManagement;
            $this->orderRepository = $orderRepository;
            $this->logger = $logger;
            
            $this->quote = $adminQuote;
            $this->session = $adminS;
            
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
    }
    
    /**
     * @inheritDoc
     */
    public function execute()
    {
        $postParams = $this->getRequest()->getPostValue();
        
        if (isset($postParams['is_exchange'])) {
            $this->session->setIsExchange($postParams['is_exchange']);
        } else {
            $this->session->unsIsExchange();
        }
        if (isset($postParams['status'])) {
            $this->session->setStatus($postParams['status']);
        } else {
            $this->session->unsStatus();
        }
        
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath('sales/order_create/index');
        return $resultRedirect;
    }
}
