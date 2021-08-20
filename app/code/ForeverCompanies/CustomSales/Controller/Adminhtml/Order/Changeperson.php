<?php

namespace ForeverCompanies\CustomSales\Controller\Adminhtml\Order;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Backend\Model\Session as AdminSession;

class Changeperson extends Action
{
    /**
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;
    protected $session;

    /**
     * Changeperson constructor.
     * @param Context $context
     * @param OrderRepositoryInterface $orderRepository
     */
    public function __construct(
        Action\Context $context,
        OrderRepositoryInterface $orderRepository,
        AdminSession $adminS
    ) {
        parent::__construct($context);
        $this->orderRepository = $orderRepository;
        $this->session = $adminS;
    }

    /**
     * @inheritDoc
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        
        $order = $this->orderRepository->get($this->getRequest()->getParam('order_id'));
        $order->setData('sales_person_id', $this->getRequest()->getParam('id'));
        $extension = $order->getExtensionAttributes();
        $extension->setSalesPersonId($this->getRequest()->getParam('id'));
        $order->setExtensionAttributes($extension);
        $this->orderRepository->save($order);
        $resultRedirect->setPath('sales/order/view', [
            'order_id' => $this->getRequest()->getParam('order_id')
        ]);
        return $resultRedirect;
    }
}
