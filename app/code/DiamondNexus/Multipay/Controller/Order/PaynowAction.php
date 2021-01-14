<?php

namespace DiamondNexus\Multipay\Controller\Order;

use Braintree\Result\Error;
use DiamondNexus\Multipay\Helper\Data;
use DiamondNexus\Multipay\Helper\EmailSender;
use DiamondNexus\Multipay\Model\ResourceModel\Transaction;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\ValidatorException;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\View\Result\PageFactory;
use Magento\PageCache\Model\Cache;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;

class PaynowAction extends Action
{
    /**
     * @var PageFactory
     */
    protected $_pageFactory;

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
     * PaynowAction constructor.
     * @param Context $context
     * @param Data $helper
     * @param OrderRepositoryInterface $orderRepository
     * @param Json $serializer
     * @param Transaction $transaction
     * @param Cache\Type $cache
     * @param PageFactory $pageFactory
     * @param EmailSender $emailSender
     */
    public function __construct(
        Context $context,
        Data $helper,
        OrderRepositoryInterface $orderRepository,
        Json $serializer,
        Transaction $transaction,
        Cache\Type $cache,
        PageFactory $pageFactory,
        EmailSender $emailSender
    ) {
        $this->_pageFactory = $pageFactory;
        $this->helper = $helper;
        $this->orderRepository = $orderRepository;
        $this->serializer = $serializer;
        $this->transaction = $transaction;
        $this->cache = $cache;
        $this->emailSender = $emailSender;
        return parent::__construct($context);
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
        /*$template = $this->emailSender->mappingTemplate('- new order');
        $this->emailSender->sendEmail($template, $order->getCustomerEmail(), ['order' => $order]);*/
        $this->cache->clean();
        return $resultRedirect->setPath('sales/order/history');
    }
}
