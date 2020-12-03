<?php

namespace ForeverCompanies\CustomApi\Plugin;

use ForeverCompanies\CustomApi\Helper\ExtOrder;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\AbstractModel;
use Magento\Sales\Model\ResourceModel\Order;

class Quote
{
    /**
     * @var ExtOrder
     */
    protected $extOrder;

    /**
     * @var Order
     */
    protected $orderResource;

    /**
     * @var string[]
     */
    protected $checkingFields = [
        'anticipated_shipdate',
        'delivery_date',
        'dispatch_date'
    ];

    /**
     * AddressRepository constructor.
     * @param ExtOrder $extOrder
     * @param Order $orderResource
     */
    public function __construct(
        ExtOrder $extOrder,
        Order $orderResource
    ) {
        $this->extOrder = $extOrder;
        $this->orderResource = $orderResource;
    }

    /**
     * Get Gift Wrapping
     *
     * @param \Magento\Quote\Model\ResourceModel\Quote $subject
     * @param AbstractModel $object
     * @return void
     * @throws LocalizedException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeSave(
        \Magento\Quote\Model\ResourceModel\Quote $subject,
        AbstractModel $object
    ) {
        if ($object->hasDataChanges()) {
            $connection = $this->orderResource->getConnection();
            $select = $connection->select()
                ->from($this->orderResource->getMainTable())
                ->where('quote_id = ?', $object->getId());
            $data = $connection->fetchRow($select);
            if (!$data) {
                return;
            }
            $orderId = $data['entity_id'];
            $changes = [];
            foreach ($this->checkingFields as $key) {
                $data = $object->getData($key);
                if ($data !== null) {
                    if ($object->dataHasChangedFor($key)) {
                        $changes[] = $key;
                    }
                }
            }
            if (count($changes) > 0) {
                $this->extOrder->createNewExtSalesOrder((int)$orderId, $changes);
            }
        }
    }
}
