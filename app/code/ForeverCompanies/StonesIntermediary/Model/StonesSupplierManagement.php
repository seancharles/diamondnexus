<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace ForeverCompanies\StonesIntermediary\Model;

use Exception;
use ForeverCompanies\StonesIntermediary\Api\Data\StonesSupplierInterface;
use ForeverCompanies\StonesIntermediary\Api\Data\StonesSupplierSearchResultsInterfaceFactory;
use ForeverCompanies\StonesIntermediary\Api\StonesSupplierManagementInterface;
use ForeverCompanies\StonesIntermediary\Model\ResourceModel\StonesSupplier\CollectionFactory;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Exception\AlreadyExistsException;

class StonesSupplierManagement implements StonesSupplierManagementInterface
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
     * @var StonesSupplierSearchResultsInterfaceFactory
     */
    protected $searchResultsFactory;

    /**
     * @var StonesSupplierFactory
     */
    protected $stonesSupplierFactory;

    /**
     * @var ResourceModel\StonesIntermediary
     */
    protected $stonesResource;

    /**
     * ExtSalesOrderUpdateManagement constructor.
     * @param CollectionFactory $collectionFactory
     * @param CollectionProcessorInterface $collectionProcessor
     * @param StonesSupplierSearchResultsInterfaceFactory $searchResultsInterfaceFactory
     * @param StonesSupplierFactory $stonesIntermediaryFactory
     * @param ResourceModel\StonesIntermediary $stonesResource
     */
    public function __construct(
        CollectionFactory $collectionFactory,
        CollectionProcessorInterface $collectionProcessor,
        StonesSupplierSearchResultsInterfaceFactory $searchResultsInterfaceFactory,
        StonesSupplierFactory $stonesIntermediaryFactory,
        \ForeverCompanies\StonesIntermediary\Model\ResourceModel\StonesIntermediary $stonesResource
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->collectionProcessor = $collectionProcessor;
        $this->searchResultsFactory = $searchResultsInterfaceFactory;
        $this->stonesSupplierFactory = $stonesIntermediaryFactory;
        $this->stonesResource = $stonesResource;
    }

    /**
     * {@inheritdoc}
     */
    public function getStonesSupplier($searchCriteria)
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
     * @throws AlreadyExistsException
     */
    public function postStonesSupplier(StonesSupplierInterface $data)
    {
        $stone = $this->stonesSupplierFactory->create();
        $stone->addData($data->getData());
        $this->stonesResource->save($stone);
        return 'New data created!';
    }

    /**
     * {@inheritdoc}
     * @throws AlreadyExistsException
     */
    public function putStonesSupplier(StonesSupplierInterface $data)
    {
        $id = $data->getId();
        if ($id == null) {
            return 'Can\'t find data without ID!';
        }
        $stone = $this->stonesSupplierFactory->create();
        $this->stonesResource->load($stone, $id);
        if ($stone->getId() == null) {
            return 'Can\'t find data by ID = ' . $id;
        }
        $stone->addData($data->getData());
        $this->stonesResource->save($stone);
        return 'New data is updated';
    }

    /**
     * {@inheritdoc}
     * @throws Exception
     */
    public function deleteStonesSupplier($id)
    {
        $stone = $this->stonesSupplierFactory->create();
        $this->stonesResource->load($stone, $id);
        if ($stone->getId() == null) {
            return 'Data is not found';
        }
        $this->stonesResource->delete($stone);
        return 'Data was deleted!';
    }
}
