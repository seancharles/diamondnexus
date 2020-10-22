<?php

namespace DiamondNexus\Multipay\Controller\Order;

use DiamondNexus\Multipay\Logger\Logger;
use DiamondNexus\Multipay\Model\Constant;
use DiamondNexus\Multipay\Model\ResourceModel\Transaction;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Result\PageFactory;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\User\Model\ResourceModel\User;
use Zend\Mail\Message;
use Zend\Mail\MessageFactory;
use Zend\Mail\Transport\Sendmail;

class PaypalAction extends Action
{
    /**
     * Holds a list of errors
     *
     * @var array
     */
    protected $errors = [];

    /**
     * @var PageFactory
     */
    protected $_pageFactory;

    /**
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @var Transaction
     */
    protected $transaction;

    /**
     * @var Session
     */
    protected $customerSession;

    /**
     * @var User
     */
    protected $userResource;

    /**
     * @var MessageFactory
     */
    protected $messageFactory;

    /**
     * @var Sendmail
     */
    protected $sendmail;

    /**
     * @var Logger
     */
    protected $logger;

    public function __construct(
        Context $context,
        PageFactory $pageFactory,
        OrderRepositoryInterface $orderRepository,
        Transaction $transaction,
        Session $customerSession,
        User $userResource,
        MessageFactory $messageFactory,
        Sendmail $sendmail,
        Logger $logger
    )
    {
        $this->_pageFactory = $pageFactory;
        $this->orderRepository = $orderRepository;
        $this->transaction = $transaction;
        $this->customerSession = $customerSession;
        $this->userResource = $userResource;
        $this->messageFactory = $messageFactory;
        $this->sendmail = $sendmail;
        $this->logger = $logger;
        return parent::__construct($context);
    }

    /**
     *  Sanitizes a string
     *
     * @param string|null $str
     * @return string
     */
    public function sanitize($str = null)
    {
        return preg_replace('/[^0-9A-Z\\.]/', '', $str);
    }

    /**
     * @return array|ResponseInterface|ResultInterface
     */
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
                $order = $this->orderRepository->get($orderId);
                $amountDue = $order->getGrandTotal() - $this->transaction->getPaidPart($orderId);
                $customerId = $this->customerSession->getCustomer()->getId();
                // make sure the current customer owns the order
                if($order->getCustomerId() == $customerId) {
                    $this->transaction->createNewTransaction($orderId, [
                        Constant::PAYMENT_METHOD_DATA => Constant::MULTIPAY_PAYPAL_OFFLINE_METHOD,
                        Constant::OPTION_TOTAL_DATA => Constant::MULTIPAY_TOTAL_AMOUNT,
                        Constant::AMOUNT_DUE_DATA => $amountDue
                    ]);
					// update the order paid amount to the grand total
					// Send salesperson an email
					$this->sendSalesPersonEmail($order, $amountDue);

				} else {
                    $result['success'] = false;
                }
            } catch (\Exception $e) {
                $result['success'] = false;
            }
		}
		return $result;
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
        $salesPerson = $salesPersonResult['firstname'].' '.$salesPersonResult['lastname'];
        $salesPersonEmail = $salesPersonResult['email'];
        $message = "A payment of $" . $amount ." was applied to order #{$order->getIncrementId()}";
        /** @var Message $mail */
        $mail = $this->messageFactory->create();
        $mail->setBody($message);
        $mail->setFrom('sales@diamondnexus.com', 'Diamond Nexus Sales');
        $mail->setSubject('Payment applied for order #' . $order->getIncrementId().' '.$storeId);
        if ( strlen($salesPersonEmail) > 0 ) {
            $salesPerson = $salesPersonResult[0]['firstname'].' '.$salesPersonResult[0]['lastname'];
            $salesPersonEmail = $salesPersonResult[0]['email'];
            $mail->addTo($salesPersonEmail, $salesPerson);
            $mail->addCc('jessica.nelson@diamondnexus.com', 'Jessica Nelson');
        } else {
            $mail->addTo('jessica.nelson@diamondnexus.com', 'Jessica Nelson');
        }
        $this->sendmail->send($mail);
    }
}
