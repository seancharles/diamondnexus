<?php

namespace DiamondNexus\Multipay\Controller\Order;

use Magento\Framework\App\Action\Action;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Mail\EmailMessage as Message;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\CsrfAwareActionInterface;
use DiamondNexus\Multipay\Helper\Data;
use DiamondNexus\Multipay\Model\Constant;

class PaypalAction extends Action implements CsrfAwareActionInterface
{
    const XML_PATH_EMAIL_RECIPIENT = '';

    protected $customerSession;
    protected $orderRepository;
    protected $userResource;
    protected $logger;
    protected $transaction;
    protected $mailHelper;
    protected $jsonResult;
    protected $scopeConfig;
    protected $helper;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\User\Model\ResourceModel\User $userResource,
        \DiamondNexus\Multipay\Logger\Logger $logger,
        \DiamondNexus\Multipay\Model\ResourceModel\Transaction $transaction,
        \ForeverCompanies\Smtp\Helper\Mail $mailHelper,
        JsonFactory $jsonResult,
        ScopeConfigInterface $scopeConfig,
        Data $helper
    ) {
        $this->customerSession = $customerSession;
        $this->orderRepository = $orderRepository;
        $this->userResource = $userResource;
        
        $this->logger = $logger;
        $this->transaction = $transaction;
        $this->mailHelper = $mailHelper;
        $this->jsonResult = $jsonResult;
        $this->scopeConfig = $scopeConfig;
        $this->helper = $helper;

        parent::__construct($context);
    }
    
    public function execute()
    {
        $resultJson = $this->jsonResult->create();

        $result = [
            'success' => true,
            'message' => ''
        ];

        if ($this->customerSession->isLoggedIn() === true) {
            $orderId = (int) $this->getRequest()->getParam('order_id');

            if ($orderId > 0) {
                try {
                    /** @var Order $order */
                    $order = $this->orderRepository->get($orderId);
                    $amountDue = $order->getGrandTotal() - $order->getTotalPaid();
                    $customerId = $this->customerSession->getCustomer()->getId();

                    $params = [
                        Constant::PAYMENT_METHOD_DATA => Constant::MULTIPAY_PAYPAL_OFFLINE_METHOD,
                        Constant::OPTION_TOTAL_DATA => Constant::MULTIPAY_TOTAL_AMOUNT,
                        Constant::AMOUNT_DUE_DATA => $amountDue,
                        Constant::NEW_BALANCE_DATA => $amountDue
                    ];

                    $order->getPayment()->setAdditionalInformation($params);

                    // make sure the current customer owns the order
                    if ($order->getCustomerId() == $customerId) {
                        $this->transaction->createNewTransaction($order, $params);
                        $this->helper->updateOrderStatus($params, $order);
                        // Send salesperson an email
                        //$this->sendSalesPersonEmail($order, $amountDue);
                    } else {
                        $result['success'] = false;
                    }
                } catch (\Exception $e) {
                    $result['success'] = false;
                    $result['message'] = $e->getMessage();
                }
            } else {
                $result['success'] = false;
                $result['message'] = "Invalid order.";
            }
        } else {
            $result['success'] = false;
            $result['message'] = "You must be logged in.";
        }

        return $resultJson->setData($result);
    }
    
    public function createCsrfValidationException(RequestInterface $request): ?       InvalidRequestException
    {
        return null;
    }
    
    public function validateForCsrf(RequestInterface $request): ?bool
    {
        return true;
    }
    
    /**
     * @param $order
     * @param $amount
     * @throws LocalizedException
     */
    protected function sendSalesPersonEmail($order, $amount)
    {
        $salesPersonId = (int)$order->getData('sales_person_id');
        $storeId = (int)$order->getData('store_id');
        
        $salesPersonSql = $this->userResource->getConnection()
            ->select()->from($this->userResource->getMainTable(), ['firstname','lastname', 'email'])
            ->where('user_id = ?', $salesPersonId);
        $salesPersonResult = $this->userResource->getConnection()->fetchRow($salesPersonSql);
        
        $salesPersonEmail = $salesPersonResult['email'];
        
        $message = "A payment of $" . $amount ." was applied to order #{$order->getIncrementId()}";
        /** @var Message $mail */

        $this->mailHelper->setFrom([
            'name' => 'Diamond Nexus Sales',
            'email' => 'sales@diamondnexus.com'
        ]);

        $ccEmail = $this->scopeConfig->getValue(
            Constant::PAYMENT_NOTIFICATION_EMAIL,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        if ($salesPersonEmail) {
            $this->mailHelper->addTo($salesPersonEmail);
        }

        if ($ccEmail) {
            $this->mailHelper->addTo($ccEmail);
        }

        $this->mailHelper->setSubject('Payment applied for order #' . $order->getIncrementId().' '.$storeId);
        $this->mailHelper->setBody($message);
        $this->mailHelper->send();
    }
}
