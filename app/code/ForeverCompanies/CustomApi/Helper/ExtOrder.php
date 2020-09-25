<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace ForeverCompanies\CustomApi\Helper;

use ForeverCompanies\CustomApi\Model\ExtSalesOrderUpdateFactory;
use ForeverCompanies\CustomApi\Model\ResourceModel\ExtSalesOrderUpdate as ExtResource;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\AlreadyExistsException;

class ExtOrder extends AbstractHelper
{
    /**
     * @var ExtSalesOrderUpdateFactory
     */
    protected $extSalesOrderUpdateFactory;

    /**
     * @var ExtResource
     */
    protected $extResource;

    public function __construct(
        Context $context,
        ExtSalesOrderUpdateFactory $extSalesOrderUpdateFactory,
        ExtResource $extResource
    ) {
        parent::__construct($context);
        $this->extSalesOrderUpdateFactory = $extSalesOrderUpdateFactory;
        $this->extResource = $extResource;
    }

    public function createNewExtSalesOrder(int $orderId, array $data)
    {
        $extOrder = $this->extSalesOrderUpdateFactory->create();
        $changesText = implode(', ', $data);
        $extOrder->setOrderId($orderId);
        $extOrder->setUpdatedFields($changesText);
        $extOrder->setFlag(0);
        try {
            $this->extResource->save($extOrder);
        } catch (AlreadyExistsException $e) {
            $this->_logger->error('Can\'t create new ExtOrder - ' . $e->getMessage());
        } catch (\Exception $e) {
            $this->_logger->error('Something went wrong when order updates - ' . $e->getMessage());
        }
    }
}
