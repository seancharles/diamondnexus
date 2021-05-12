<?php

namespace DiamondNexus\Multipay\Controller\Order;

use Magento\Backend\App\Action\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\ValidatorException;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\View\Result\PageFactory;

use Magento\PageCache\Model\Cache;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Framework\Message\ManagerInterface;

use Braintree\Result\Error;
use DiamondNexus\Multipay\Helper\Data;
use DiamondNexus\Multipay\Helper\EmailSender;
use DiamondNexus\Multipay\Model\ResourceModel\Transaction;

class PaynowAction implements \Magento\Framework\App\Action\HttpPostActionInterface
{
    /**
     * @var PageFactory
     */
    protected $pageFactory;

    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @var Json
     */
    protected $serializer;

    /**
     * @var Transaction
     */
    protected $transaction;

    /**
     * @var Cache\Type
     */
    protected $cache;

    /**
     * @var EmailSender
     */
    protected $emailSender;

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
     * @var ManagerInterface
     */
    protected $messageManager;

    /**
     * PaynowAction constructor.
     * @param Context $context
     * @param RedirectFactory $redirectFactory
     * @param PageFactory $pageFactory
     * @param Data $helper
     * @param OrderRepositoryInterface $orderRepository
     * @param Json $serializer
     * @param Transaction $transaction
     * @param Cache\Type $cache
     * @param EmailSender $emailSender
     */
    public function __construct(
        Context $context,
        RedirectFactory $redirectFactory,
        PageFactory $pageFactory,
        Data $helper,
        OrderRepositoryInterface $orderRepository,
        Json $serializer,
        Transaction $transaction,
        Cache\Type $cache,
        EmailSender $emailSender,
        ManagerInterface $messageManager
    ) {
        $this->pageFactory = $pageFactory;
        $this->request = $context->getRequest();
        $this->response = $context->getResponse();
        $this->resultRedirectFactory = $redirectFactory;
        $this->resultFactory = $context->getResultFactory();
        $this->helper = $helper;
        $this->orderRepository = $orderRepository;
        $this->serializer = $serializer;
        $this->transaction = $transaction;
        $this->cache = $cache;
        $this->emailSender = $emailSender;
        $this->messageManager = $messageManager;
    }

    /**
     * @return ResponseInterface|Redirect|ResultInterface
     */
    public function execute()
    {
        $errors = [];
        
        $resultRedirect = $this->resultRedirectFactory->create();
        $params = $this->getRequest()->getParams();

        /** @var Order $order */
        $order = $this->orderRepository->get($params['order_id']);

        $amountDue = $order->getGrandTotal() - $order->getTotalPaid();;
        $amountToPay = $params['multipay_option_partial'];
        $optionTotal = $params['multipay_option_total'];
        
        $ccNumber = $params['multipay_cc_number'];
        $ccCVV = $params['multipay_cvv_number'];
        $ccExpMonth = $params['multipay_cc_exp_month'];
        $ccExpYear = $params['multipay_cc_exp_year'];
        
        // basic validation
        if($optionTotal == 1) {
            // do nothing
        } elseif($optionTotal == 2) {
            if($amountToPay > $amountDue ) {
                $errors[] = "Invalid payment amount, may not be more than total due.";
            }
        } else {
            $errors[] = "Please select total amount or partial amount.";
        }
        
        if(!(strlen($ccNumber) == 15 || strlen($ccNumber) == 16)) {
            $errors[] = "Invalid credit card number entered";
        }
        
        if(!(strlen($ccCVV) == 3 || strlen($ccCVV) == 4)) {
            $errors[] = "Invalid CVV number entered.";
        }
        
        if($ccExpMonth < 1 || $ccExpMonth > 12) {
            $errors[] = "Invalid expiration month.";
        }    

        if(count($errors) == 0) {
            try{
                $order->getPayment()->setAdditionalData($this->serializer->serialize($params));
                $order->getPayment()->setAdditionalInformation($params);
                
                // process braintree payment info
                $this->transaction->createNewTransaction($order, $params);
                $this->helper->updateOrderStatus($params, $order);
                $this->cache->clean();
                
                // add success message
                $this->messageManager->addSuccess(__("Payment was applied successfully. An email has been sent as a receipt."));
                
                return $resultRedirect->setPath('sales/order/history');
                
            } catch(ValidatorException $e) {
                // add error to session
                $this->messageManager->addError(__($e->getMessage()));
            }
        } else {
            foreach($errors as $error) {
                $this->messageManager->addError(__($error));
            }
        }
        
        return $resultRedirect->setPath('*/*/paynow/order_id/' . $params['order_id'] . "?openform=true");
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
