<?php

namespace ForeverCompanies\CustomApi\Plugin;

use ForeverCompanies\CustomApi\Api\Data\CustomOptionInterfaceFactory;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderItemExtension;
use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Sales\Api\Data\OrderItemExtensionFactory;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Api\Data\OrderSearchResultInterface;

class OrderItemCustomOptions
{
    /**
     * @var OrderItemExtensionFactory
     */
    private $extensionFactory;

    /**
     * @var CustomOptionInterfaceFactory
     */
    private $customOptionInterfaceFactory;

    /**
     * @var ResourceConnection
     */
    private $db;

    /**
     * Init plugin
     *
     * @param OrderItemExtensionFactory $extensionFactory
     * @param CustomOptionInterfaceFactory $customOptionInterfaceFactory
     * @param ResourceConnection $db
     */
    public function __construct(
        OrderItemExtensionFactory $extensionFactory,
        CustomOptionInterfaceFactory $customOptionInterfaceFactory,
        ResourceConnection $db
    ) {
        $this->extensionFactory = $extensionFactory;
        $this->customOptionInterfaceFactory = $customOptionInterfaceFactory;
        $this->db = $db;
    }

    /**
     * Get Gift Wrapping
     *
     * @param OrderRepositoryInterface $subject
     * @param OrderInterface $resultEntity
     * @return OrderInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGet(
        OrderRepositoryInterface $subject,
        OrderInterface $resultEntity
    ) {

        foreach ($resultEntity->getItems() as $orderItem) {
            $this->processOrderItemCustomOptions($orderItem);
        }

        return $resultEntity;
    }

    /**
     * @param OrderRepositoryInterface $subject
     * @param OrderSearchResultInterface $resultEntity
     * @return OrderSearchResultInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetList(
        OrderRepositoryInterface $subject,
        OrderSearchResultInterface $resultEntity
    ) {
        foreach ($resultEntity->getItems() as $entity) {
            $this->afterGet($subject, $entity);
        }
        return $resultEntity;
    }

    /**
     * @param OrderItemInterface $resultEntity
     */
    private function processOrderItemCustomOptions(OrderItemInterface $resultEntity)
    {
        /** @var OrderItemExtension $extensionAttributes */
        $extensionAttributes = $resultEntity->getExtensionAttributes();
        if ($extensionAttributes === null) {
            $extensionAttributes = $this->extensionFactory->create();
        }
        $customOptions = [];
        if (!isset($resultEntity->getProductOptions()['info_buyRequest']['options'])) {
            return;
        }
        $options = $resultEntity->getProductOptions()['info_buyRequest']['options'];
        if ($options == null || count($options) == 0) {
            return;
        }
        $idTable = 'catalog_product_option_title';
        $valueTable = 'catalog_product_option_type_title';
        $connection = $this->db->getConnection();
        foreach ($options as $id => $value) {
            $name = $connection->select()->from($idTable)->where('option_id = ' . $id);
            $val = $connection->select()->from($valueTable)->where('option_type_id = ' . $value);
            $customOption = $this->customOptionInterfaceFactory->create();
            $customOption->setOptionId($id);
            $customOption->setOptionValue($value);
            if ($id !== '') {
                $rowName = $connection->fetchRow($name);
                if ($rowName !== false) {
                    $customOption->setOptionTitle($rowName['title']);
                }
            } else {
                $customOption->setOptionTitle('option not found in catalog_product_option table');
            }
            if ($value !== '') {
                $rowVal = $connection->fetchRow($val);
                if ($rowVal !== false) {
                    $customOption->setOptionValueTitle($rowVal['title']);
                } else {
                    $customOption->setOptionValueTitle($value);
                }
            } else {
                $customOption->setOptionValueTitle('');
            }
            $customOptions[] = $customOption;
        }
        $extensionAttributes->setCustomOptions($customOptions);
        $resultEntity->setExtensionAttributes($extensionAttributes);
    }
}
