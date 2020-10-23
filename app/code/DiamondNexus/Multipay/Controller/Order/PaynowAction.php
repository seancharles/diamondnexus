<?php

namespace DiamondNexus\Multipay\Controller\Order;

use Braintree\Result\Error;
use DiamondNexus\Multipay\Helper\Data;
use DiamondNexus\Multipay\Model\ResourceModel\Transaction;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Exception\ValidatorException;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\View\Result\PageFactory;
use Magento\PageCache\Model\Cache;
use Magento\Sales\Api\OrderRepositoryInterface;

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

    public function __construct(
        Context $context,
        Data $helper,
        OrderRepositoryInterface $orderRepository,
        Json $serializer,
        Transaction $transaction,
        Cache\Type $cache,
        PageFactory $pageFactory
    ) {
        $this->_pageFactory = $pageFactory;
        $this->helper = $helper;
        $this->orderRepository = $orderRepository;
        $this->serializer = $serializer;
        $this->transaction = $transaction;
        $this->cache = $cache;
        return parent::__construct($context);
    }

    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $params = $this->getRequest()->getParams();
        $order = $this->orderRepository->get($params['order_id']);
        $order->getPayment()->setAdditionalData($this->serializer->serialize($params));
        $order->getPayment()->setAdditionalInformation($params);
        $this->transaction->createNewTransaction($order, $params);
        $this->helper->updateOrderStatus($params, $order);
        $this->cache->clean();
        return $resultRedirect->setPath('sales/order/history');
    }
}
