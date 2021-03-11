<?php

namespace DiamondNexus\Multipay\Controller\Order;

use Braintree\Result\Error;
use DiamondNexus\Multipay\Helper\Data;
use DiamondNexus\Multipay\Helper\EmailSender;
use DiamondNexus\Multipay\Model\ResourceModel\Transaction;
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
        EmailSender $emailSender
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
    }

    /**
     * @return ResponseInterface|Redirect|ResultInterface
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $params = $this->getRequest()->getParams();
        /** @var Order $order */
        $order = $this->orderRepository->get($params['order_id']);
        $order->getPayment()->setAdditionalData($this->serializer->serialize($params));
        $order->getPayment()->setAdditionalInformation($params);
        $this->transaction->createNewTransaction($order, $params);
        $this->helper->updateOrderStatus($params, $order);
        $this->cache->clean();
        return $resultRedirect->setPath('sales/order/history');
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
