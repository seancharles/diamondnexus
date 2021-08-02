<?php
namespace DiamondNexus\Multipay\Block\Order;

use DiamondNexus\Multipay\Model\Constant;
use DiamondNexus\Multipay\Model\ResourceModel\Transaction;
use Magento\Backend\Block\Template\Context;
use Magento\CustomerBalance\Model\BalanceFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Message\ManagerInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Store\Model\ScopeInterface;

class Paynow extends AbstractPay
{
    protected $balanceFactory;

    public function __construct(
        Context $context,
        OrderRepositoryInterface $orderRepository,
        Transaction $transaction,
        ManagerInterface $messageManager,
        BalanceFactory $balanceFactory
    ) {
        parent::__construct($context, $orderRepository, $transaction, $messageManager);

        $this->balanceFactory = $balanceFactory;
    }

    /**
     * @return mixed|string
     */
    public function getClientId()
    {
        try {
            $id = $this->_storeManager->getStore()->getId();
            return $this->_scopeConfig->getValue(Constant::CLIENT_XML, ScopeInterface::SCOPE_STORE, $id);
        } catch (NoSuchEntityException $e) {
            return '';
        }
    }

    /**
     * @return mixed|string
     */
    public function getPaypalLogo()
    {
        try {
            $id = $this->_storeManager->getStore()->getId();
            return $this->_scopeConfig->getValue('paypal/style/logo', ScopeInterface::SCOPE_STORE, $id);
        } catch (NoSuchEntityException $e) {
            return '';
        }
    }

    public function getStoreCreditAmount()
    {
        $customerId = $this->getData('order')->getCustomerId();
        $totalDue = $this->getData('order')->getTotalDue();
        if ($customerId > 0) {
            $balanceModel = $this->balanceFactory->create();
            $balanceModel->setCustomerId($customerId)->loadByCustomer();

            if (round($totalDue, 2) < round($balanceModel->getAmount(), 2)) {
                return $totalDue;
            } else {
                return $balanceModel->getAmount();
            }
        } else {
            return 0;
        }
    }

    /**
     * @return MessageInterface|MessageInterface[]
     */
    public function getErrors()
    {
        return $this->messageManager->getMessages()->getErrors();
    }

    public function getPaypalActionUrl()
    {
        return $this->getUrl('diamondnexus/order/paypalAction');
    }

    public function getPaypalPaymentCompleteUrl($orderId = 0)
    {
        return $this->getUrl('diamondnexus/order/paynowComplete/', ['order_id' => $orderId]);
    }
}
