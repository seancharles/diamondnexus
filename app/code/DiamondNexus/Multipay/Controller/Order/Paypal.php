<?php

namespace DiamondNexus\Multipay\Controller\Order;

use Braintree\Result\Error;
use DiamondNexus\Multipay\Helper\Data;
use DiamondNexus\Multipay\Logger\Logger;
use DiamondNexus\Multipay\Model\ResourceModel\Transaction;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Exception\ValidatorException;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\View\Result\PageFactory;
use Magento\PageCache\Model\Cache;
use Magento\Sales\Api\OrderRepositoryInterface;

class Paypal extends Action
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

    protected $logger;

    public function __construct(
        Context $context,
        PageFactory $pageFactory,
        Logger $logger
    )
    {
        $this->_pageFactory = $pageFactory;
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
     * Index action method
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('order_id');
        $page = $this->_pageFactory->create();
        /** @var \DiamondNexus\Multipay\Block\Order\Paypal $block */
        $block = $page->getLayout()->getBlock('diamondnexus_paypal');
        $block->setData('order_id', $id);
        return $page;
    }

    public function processAction()
    {
        // Get the order id
        $orderId = $this->getRequest()->getParam('order_id');

        $result = [
            'success' => true,
            'message' => ''
        ];
		if ($orderId > 0) {
			try {
                $order = Mage::getSingleton('sales/order')->load($orderId);

                $amountDue = $order->getGrandTotal() - $order->getTotalPaid();

                $customerId = Mage::getSingleton('customer/session')->getCustomer()->getId();

                // make sure the current customer owns the order
                if($order->getCustomerId() == $customerId) {

                    $transaction = Mage::getSingleton('diamondnexus_multipay/transaction');
					// create a cash payment transaction //
					$transaction->setData(array(
                        'order_id' => $order->getId(),
                        'action' => DiamondNexus_Multipay_Model_Constant::MULTIPAY_SALE_ACTION,
                        'method' => DiamondNexus_Multipay_Model_Constant::MULTIPAY_PAYPAL_OFFLINE_METHOD,
                        'amount' => $amountDue,
                        'tendered' => 0,
                        'change' => 0,
                        'timestamp' => time()
                    ));

					$transaction->save();
					// update the order paid amount to the grand total
					$order->setTotalPaid($order->getGrandTotal());
					$order->setBaseTotalPaid($order->getGrandTotal());
					if ($order->canInvoice()) {
						$invoice = Mage::getModel('sales/service_order', $order)->prepareInvoice();
						$invoice->register();
						$transactionSave = Mage::getModel('core/resource_transaction')
                            ->addObject($invoice)
                            ->addObject($invoice->getOrder());
						$transactionSave->save();
					}

					$order->setState(Mage_Sales_Model_Order::STATE_PROCESSING, true);
					$order->sendOrderUpdateEmail(true, 'Order Complete'); //Sending email notification to customer

					Mage::dispatchEvent('diamondnexus_shipping_add_dates', array('order' => $order));
					//Mage::dispatchEvent('add_anticipated_shipdate_update_event', array('order' => $order));
					$order->save();
					// Send salesperson an email
					$this->sendSalesPersonEmail($order, $amountDue);

					Mage::dispatchEvent( 'diamondnexus_multipay_payment_add', array('transaction'=>$transaction,'order'=>$order) );

				} else {
                    $result['success'] = false;
                }
            } catch (Exception $e) {
                $result['success'] = false;
                //$result['message'] $e->getMessage();
            }
		}

		echo json_encode($result);
	}

    public function paymentAddedAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }

    public function paymentCompleteAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }

    /**
     * is Secure
     */
    public function isSecure($orderId)
    {
        if ( $_SERVER['HTTPS'] != 'on' ) {
            header( "Location: " . Mage::getUrl("multipay/makePayment/index/order_id/$orderId",array('_secure'=>true)));
            exit;
        }
    }

    private function sendSalesPersonEmail($order, $amount)
    {
        $salesPersonId = (int)$order->getData('sales_person_id');
        $storeId = (int)$order->getData('store_id');
        $newres = Mage::getSingleton('core/resource');
        $newdb = $newres->getConnection('sales_read');
        $salesPersonsql = $newdb->select()
            ->from('admin_user', array('firstname','lastname', 'email'))
            ->where('user_id=?', $salesPersonId, Zend_Db::INT_TYPE);
        $salesPersonResult = $newdb->fetchAll($salesPersonsql);
        $salesPerson = $salesPersonResult[0]['firstname'].' '.$salesPersonResult[0]['lastname'];
        $salesPersonEmail = $salesPersonResult[0]['email'];
        $message = "A payment of $" . $amount ." was applied to order #{$order->getIncrementId()}";
        $mail = new Zend_Mail();
        $mail->setBodyText($message);
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
        $mail->send();
    }
}
