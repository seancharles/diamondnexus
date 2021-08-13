<?php

namespace ForeverCompanies\CustomSales\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Model\Order;

/**
 * Save original order status.
 */
class AutoInvoiceOrder implements ObserverInterface
{
    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Invoice\CollectionFactory
     */
    protected $invoiceCollectionFactory;

    /**
     * @var \Magento\Sales\Api\InvoiceRepositoryInterface
     */
    protected $invoiceRepository;

    /**
     * @var \Magento\Sales\Model\Service\InvoiceService
     */
    protected $invoiceService;

    /**
     * @var \Magento\Framework\DB\TransactionFactory
     */
    protected $transactionFactory;

    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @param \Magento\Sales\Model\ResourceModel\Order\Invoice\CollectionFactory $invoiceCollectionFactory
     * @param \Magento\Sales\Model\Service\InvoiceService $invoiceService
     * @param \Magento\Framework\DB\TransactionFactory $transactionFactory
     * @param \Magento\Sales\Api\InvoiceRepositoryInterface $invoiceRepository
     */
    public function __construct(
        \Magento\Sales\Model\ResourceModel\Order\Invoice\CollectionFactory $invoiceCollectionFactory,
        \Magento\Sales\Model\Service\InvoiceService $invoiceService,
        \Magento\Framework\DB\TransactionFactory $transactionFactory,
        \Magento\Sales\Api\InvoiceRepositoryInterface $invoiceRepository
    ) {
          $this->invoiceCollectionFactory = $invoiceCollectionFactory;
          $this->invoiceService = $invoiceService;
          $this->transactionFactory = $transactionFactory;
          $this->invoiceRepository = $invoiceRepository;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /** @var Order $order */
        $order = $observer->getEvent()->getOrder();

        //order is new
        if (!$order->getId()) {
            return $this;
        }

        // only invoice if the order has been paid in full or has no payment required.
        if ($order->getTotalPaid() == $order->getGrandTotal() || $order->getGrandTotal() == 0) {
            $this->createInvoice($order);
        }
    }

    protected function createInvoice($order)
    {
        try {
            if ($order) {
                $invoices = $this->invoiceCollectionFactory->create()
                  ->addAttributeToFilter('order_id', ['eq' => $order->getId()]);

                $invoices->getSelect()->limit(1);

                if ((int)$invoices->count() !== 0) {
                    $invoices = $invoices->getFirstItem();
                    $invoice = $this->invoiceRepository->get($invoices->getId());
                    return $invoice;
                }

                if (!$order->canInvoice()) {
                    return null;
                }

                $invoice = $this->invoiceService->prepareInvoice($order);
                $invoice->setRequestedCaptureCase(\Magento\Sales\Model\Order\Invoice::CAPTURE_ONLINE);
                $invoice->register();
                $invoice->getOrder()->setCustomerNoteNotify(false);
                $invoice->getOrder()->setIsInProcess(true);
                $transactionSave = $this->transactionFactory->create()->addObject(
                    $invoice
                )->addObject(
                    $invoice->getOrder()
                );
                $transactionSave->save();

                return $invoice;
            }
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __($e->getMessage())
            );
        }
    }
}
