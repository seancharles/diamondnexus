<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace ForeverCompanies\StonesIntermediary\Model;

use Exception;
use ForeverCompanies\StonesIntermediary\Api\Data\StonesIntermediaryInterface;
use ForeverCompanies\StonesIntermediary\Api\Data\StonesIntermediarySearchResultsInterfaceFactory;
use ForeverCompanies\StonesIntermediary\Api\StonesIntermediaryManagementInterface;
use ForeverCompanies\StonesIntermediary\Model\ResourceModel\StonesIntermediary\CollectionFactory;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Exception\AlreadyExistsException;

class StonesIntermediaryManagement implements StonesIntermediaryManagementInterface
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
     * @var StonesIntermediarySearchResultsInterfaceFactory
     */
    protected $searchResultsFactory;

    /**
     * @var StonesIntermediaryFactory
     */
    protected $stonesIntermediaryFactory;

    /**
     * @var ResourceModel\StonesIntermediary
     */
    protected $stonesResource;

    /**
     * ExtSalesOrderUpdateManagement constructor.
     * @param CollectionFactory $collectionFactory
     * @param CollectionProcessorInterface $collectionProcessor
     * @param StonesIntermediarySearchResultsInterfaceFactory $searchResultsInterfaceFactory
     * @param StonesIntermediaryFactory $stonesIntermediaryFactory
     * @param ResourceModel\StonesIntermediary $stonesResource
     */
    public function __construct(
        CollectionFactory $collectionFactory,
        CollectionProcessorInterface $collectionProcessor,
        StonesIntermediarySearchResultsInterfaceFactory $searchResultsInterfaceFactory,
        StonesIntermediaryFactory $stonesIntermediaryFactory,
        \ForeverCompanies\StonesIntermediary\Model\ResourceModel\StonesIntermediary $stonesResource
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->collectionProcessor = $collectionProcessor;
        $this->searchResultsFactory = $searchResultsInterfaceFactory;
        $this->stonesIntermediaryFactory = $stonesIntermediaryFactory;
        $this->stonesResource = $stonesResource;
    }

    /**
     * {@inheritdoc}
     */
    public function getStonesIntermediary($searchCriteria)
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
    public function postStonesIntermediary(StonesIntermediaryInterface $data)
    {
        $stone = $this->stonesIntermediaryFactory->create();
        $stone->addData($data->getData());
        $this->stonesResource->save($stone);
        return 'New data created!';
    }

    /**
     * {@inheritdoc}
     * @throws AlreadyExistsException
     */
    public function putStonesIntermediary(StonesIntermediaryInterface $data)
    {
        $id = $data->getId();
        if ($id == null) {
            return 'Can\'t find data without ID!';
        }
        $stone = $this->stonesIntermediaryFactory->create();
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
    public function deleteStonesIntermediary($id)
    {
        $stone = $this->stonesIntermediaryFactory->create();
        $this->stonesResource->load($stone, $id);
        if ($stone->getId() == null) {
            return 'Data is not found';
        }
        $this->stonesResource->delete($stone);
        return 'Data was deleted!';
    }
}
