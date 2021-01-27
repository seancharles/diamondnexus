<?php

namespace DiamondNexus\Multipay\Plugin\Model;

use DiamondNexus\Multipay\Model\Constant;
use DiamondNexus\Multipay\Model\ResourceModel\Transaction;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Api\Data\OrderExtension;
use Magento\Sales\Api\Data\OrderExtensionFactory;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\ResourceModel\Order\Collection;

class OrderGet
{
    /**
     * @var OrderExtensionFactory
     */

    protected $orderExtensionFactory;
    /**
     * @var Transaction
     */
    protected $transactionResource;

    /**
     * Init plugin
     *
     * @param OrderExtensionFactory $orderExtensionFactory
     * @param Transaction $transactionResource
     */
    public function __construct(
        OrderExtensionFactory $orderExtensionFactory,
        Transaction $transactionResource
    ) {
        $this->orderExtensionFactory = $orderExtensionFactory;
        $this->transactionResource = $transactionResource;
    }

    /**
     * @param OrderRepositoryInterface $subject
     * @param OrderInterface $resultOrder
     * @return OrderInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @throws LocalizedException
     */
    public function afterGet(
        OrderRepositoryInterface $subject,
        OrderInterface $resultOrder
    ) {
        $extensionAttributes = $resultOrder->getExtensionAttributes();
        if ($extensionAttributes && $extensionAttributes->getMultipayTransactions()) {
            return $resultOrder;
        }

        /** @var OrderExtension $orderExtension */
        $orderExtension = $extensionAttributes ? $extensionAttributes : $this->orderExtensionFactory->create();
        $allTransactionsByOrderId = $this->transactionResource->getAllTransactionsByOrderId($resultOrder->getId());
        foreach ($allTransactionsByOrderId as &$transaction) {
            if (isset($transaction['payment_method'])) {
                $transaction['payment_method'] = Constant::MULTIPAY_METHOD_LABEL[$transaction['payment_method']];
            }
        }
        if (count($allTransactionsByOrderId) > 0) {
            $orderExtension->setMultipayTransactions($allTransactionsByOrderId);
            $resultOrder->setExtensionAttributes($orderExtension);
        }

        return $resultOrder;
    }

    /**
     * @param OrderRepositoryInterface $subject
     * @param Collection $resultOrder
     * @return Collection
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @throws LocalizedException
     */
    public function afterGetList(
        OrderRepositoryInterface $subject,
        Collection $resultOrder
    ) {
        /** @var  $order */
        foreach ($resultOrder->getItems() as $order) {
            $this->afterGet($subject, $order);
        }
        return $resultOrder;
    }
}
