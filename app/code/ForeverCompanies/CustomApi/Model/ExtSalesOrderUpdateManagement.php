<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace ForeverCompanies\CustomApi\Model;

use ForeverCompanies\CustomApi\Api\Data\ExtSearchResultsInterfaceFactory;
use ForeverCompanies\CustomApi\Api\ExtSalesOrderUpdateManagementInterface;
use ForeverCompanies\CustomApi\Helper\ExtOrder;
use ForeverCompanies\CustomApi\Model\ResourceModel\ExtSalesOrderUpdate\CollectionFactory;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;

class ExtSalesOrderUpdateManagement implements ExtSalesOrderUpdateManagementInterface
{
    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var CollectionProcessorInterface
     */
    protected $collectionProcessor;

    /**
     * @var ExtSearchResultsInterfaceFactory
     */
    protected $searchResultsFactory;

    /**
     * @var ExtOrder
     */
    protected $helper;

    /**
     * ExtSalesOrderUpdateManagement constructor.
     * @param CollectionFactory $collectionFactory
     * @param CollectionProcessorInterface $collectionProcessor
     * @param ExtSearchResultsInterfaceFactory $searchResultsInterfaceFactory
     * @param ExtOrder $helper
     */
    public function __construct(
        CollectionFactory $collectionFactory,
        CollectionProcessorInterface $collectionProcessor,
        ExtSearchResultsInterfaceFactory $searchResultsInterfaceFactory,
        ExtOrder $helper
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->collectionProcessor = $collectionProcessor;
        $this->searchResultsFactory = $searchResultsInterfaceFactory;
        $this->helper = $helper;
    }

    /**
     * {@inheritdoc}
     */
    public function getExtSalesOrderUpdate($searchCriteria)
    {
        $collection = $this->collectionFactory->create();
        $this->collectionProcessor->process($searchCriteria, $collection);
        $collection->load();
        $searchResult = $this->searchResultsFactory->create();
        $searchResult->setSearchCriteria($searchCriteria);
        $searchResult->setItems($collection->getItems());
        $searchResult->setTotalCount($collection->getSize());
        return $searchResult;
    }

    /**
     * {@inheritdoc}
     */
    public function postExtSalesOrderUpdate($orderId, $updatedFields, $flagFishbowlUpdate)
    {
        $this->helper->createNewExtSalesOrder($orderId, $updatedFields, $flagFishbowlUpdate);
        return 'Success';
    }

    /**
     * {@inheritdoc}
     */
    public function putExtSalesOrderUpdate(int $entityId, bool $flagFishbowlUpdate)
    {
        return $this->helper->updateExtSalesOrder($entityId, $flagFishbowlUpdate);
    }
}
