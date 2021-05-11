<?php

namespace DiamondNexus\Multipay\Controller\Order;

use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Mail\EmailMessage as Message;

use DiamondNexus\Multipay\Model\Constant;

class PaypalAction extends \Magento\Framework\App\Action\Action implements \Magento\Framework\App\CsrfAwareActionInterface
{
    public function __construct (
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession, 
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\User\Model\ResourceModel\User $userResource,
        
        \DiamondNexus\Multipay\Logger\Logger $logger,
        \DiamondNexus\Multipay\Model\ResourceModel\Transaction $transaction,
        \ForeverCompanies\Smtp\Helper\Mail $mailHelper,
    ) {
        $this->customerSession = $customerSession;
        $this->orderRepository = $orderRepository;
        $this->userResource = $userResource;
        
        $this->logger = $logger;
        $this->transaction = $transaction;
        $this->mailHelper = $mailHelper;
        
        parent::__construct($context); 
    }
    
    public function execute()
    {
        // Get the order id
        $orderId = $this->getRequest()->getParam('order_id');

        $result = [
            'success' => true,
            'message' => ''
        ];
        if ($orderId > 0) {
            try {
                /** @var Order $order */
                $order = $this->orderRepository->get($orderId);
                $amountDue = $order->getGrandTotal() - $this->transaction->getPaidPart($orderId);
                $customerId = $this->customerSession->getCustomer()->getId();
                // make sure the current customer owns the order
                if ($order->getCustomerId() == $customerId) {
                    $this->transaction->createNewTransaction($order, [
                        Constant::PAYMENT_METHOD_DATA => Constant::MULTIPAY_PAYPAL_OFFLINE_METHOD,
                        Constant::OPTION_TOTAL_DATA => Constant::MULTIPAY_TOTAL_AMOUNT,
                        Constant::AMOUNT_DUE_DATA => $amountDue
                    ]);
                    // Send salesperson an email
                    $this->sendSalesPersonEmail($order, $amountDue);
                } else {
                    $result['success'] = false;
                }
            } catch (\Exception $e) {
                $result['success'] = false;
                $result['message'] = $e->getMessage();
            }
        }
        print_r(json_encode($result));
    }
    
    public function createCsrfValidationException( RequestInterface $request ): ?       InvalidRequestException { 
        return null; 
    } 
    
    public function validateForCsrf(RequestInterface $request): ?bool {     
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

        $this->mailHelper->setFrom(
            'name' => 'Diamond Nexus Sales',
            'email' => 'sales@diamondnexus.com'
        );
        $this->mailHelper->addTo([$salesPersonEmail,'jessica.nelson@diamondnexus.com']);
        $this->mailHelper->setSubject('Payment applied for order #' . $order->getIncrementId().' '.$storeId);
        $this->mailHelper->setBody($message);
        $this->mailHelper->send();
    }
}
