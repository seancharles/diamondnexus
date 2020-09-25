<?php

namespace ForeverCompanies\CustomApi\Plugin;

use ForeverCompanies\CustomApi\Helper\ExtOrder;
use Magento\Sales\Api\Data\OrderAddressInterface;

class AddressRepository
{
    /**
     * @var ExtOrder
     */
    protected $extOrder;

    /**
     * AddressRepository constructor.
     * @param ExtOrder $extOrder
     */
    public function __construct(
        ExtOrder $extOrder
    ) {
        $this->extOrder = $extOrder;
    }

    /**
     * Check address has changed
     *
     * @param \Magento\Sales\Model\Order\AddressRepository $subject
     * @param OrderAddressInterface $entity
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeSave(
        \Magento\Sales\Model\Order\AddressRepository $subject,
        OrderAddressInterface $entity
    ) {
        if ($entity->hasDataChanges() && $entity->getOrder()->getId()) {
            $this->extOrder->createNewExtSalesOrder($entity->getOrder()->getId(), ['shipping_address']);
        }
    }
}
