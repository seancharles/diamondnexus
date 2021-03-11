<?php

namespace ForeverCompanies\AssignCustomerToOrder\Controller\Adminhtml\Customer;

use Exception;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\Auth\Session;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderCustomerManagementInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use ForeverCompanies\AssignCustomerToOrder\Helper\Data;

/**
 * Class Index
 * @package ForeverCompanies\AssignCustomerToOrder\Controller\Adminhtml\Customer
 */
class Index extends Action implements \Magento\Framework\App\Action\HttpPostActionInterface
{
    /**
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @var AccountManagementInterface
     */
    protected $accountManagement;

    /**
     * @var OrderCustomerManagementInterface
     */
    protected $orderCustomerService;

    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var Data
     */
    protected $helperData;

    /**
     * @var Session
     */
    private $authSession;

    /**
     * Index constructor.
     * @param Context $context
     * @param OrderRepositoryInterface $orderRepository
     * @param AccountManagementInterface $accountManagement
     * @param OrderCustomerManagementInterface $orderCustomerService
     * @param JsonFactory $resultJsonFactory
     * @param Session $authSession
     * @param Data $helperData
     */
    public function __construct(
        Context $context,
        OrderRepositoryInterface $orderRepository,
        AccountManagementInterface $accountManagement,
        OrderCustomerManagementInterface $orderCustomerService,
        JsonFactory $resultJsonFactory,
        Session $authSession,
        Data $helperData
    )
    {
        parent::__construct($context);
        $this->orderRepository = $orderRepository;
        $this->orderCustomerService = $orderCustomerService;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->accountManagement = $accountManagement;
        $this->authSession = $authSession;
        $this->helperData = $helperData;
    }

    /**
     * Index action
     * @return Json
     */
    public function execute()
    {
        /** @var \Magento\Framework\App\Request\Http $request */
        $request = $this->getRequest();
        $orderId = $request->getPost('order_id', null);
        $resultJson = $this->resultJsonFactory->create();
        $order = $this->orderRepository->get($orderId);
        if ($orderId && $order->getEntityId()) {
            try {
                $this->helperData->setCustomerData($order);
                $comment = sprintf(
                    __("Guest order converted by admin user: %s"),
                    $this->authSession->getUser()->getName()
                );
                $order->addStatusHistoryComment($comment);
                $this->orderRepository->save($order);
                $this->helperData->dispatchCustomerOrderLinkEvent($order->getCustomerId(), $order->getIncrementId());
                $this->messageManager->addSuccessMessage(__('Order was successfully converted.'));
                return $resultJson->setData($this->getMessage(false, 'Order successfully converted.'));
            } catch (Exception $e) {
                return $resultJson->setData($this->getMessage(true, $e->getMessage()));
            }
        } else {
            return $resultJson->setData($this->getMessage(true, 'Invalid order id.'));
        }
    }

    protected function getMessage($hasError, $message)
    {
        return [
            'error' => $hasError,
            'message' => __($message)
        ];
    }
}
