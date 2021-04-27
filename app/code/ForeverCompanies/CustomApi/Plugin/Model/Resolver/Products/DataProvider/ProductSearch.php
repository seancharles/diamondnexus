<?php

namespace ForeverCompanies\CustomApi\Plugin\Model\Resolver\Products\DataProvider;

use Magento\Catalog\Api\Data\ProductSearchResultsInterfaceFactory;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\CatalogGraphQl\Model\Resolver\Products\DataProvider\Product\CollectionPostProcessor;
use Magento\CatalogGraphQl\Model\Resolver\Products\DataProvider\Product\CollectionProcessorInterface;
use Magento\CatalogGraphQl\Model\Resolver\Products\DataProvider\ProductSearch\ProductCollectionSearchCriteriaBuilder;
use Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection\SearchResultApplierFactory;
use Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection\SearchResultApplierInterface;
use Magento\Framework\Api\Search\SearchResultInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchResultsInterface;
use Magento\GraphQl\Model\Query\ContextInterface;

class ProductSearch
{
    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var ProductSearchResultsInterfaceFactory
     */
    private $searchResultsFactory;

    /**
     * @var CollectionProcessorInterface
     */
    private $collectionPreProcessor;

    /**
     * @var CollectionPostProcessor
     */
    private $collectionPostProcessor;

    /**
     * @var SearchResultApplierFactory;
     */
    private $searchResultApplierFactory;

    /**
     * @var ProductCollectionSearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @param CollectionFactory $collectionFactory
     * @param ProductSearchResultsInterfaceFactory $searchResultsFactory
     * @param CollectionProcessorInterface $collectionPreProcessor
     * @param CollectionPostProcessor $collectionPostProcessor
     * @param SearchResultApplierFactory $searchResultsApplierFactory
     * @param ProductCollectionSearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        CollectionFactory $collectionFactory,
        ProductSearchResultsInterfaceFactory $searchResultsFactory,
        CollectionProcessorInterface $collectionPreProcessor,
        CollectionPostProcessor $collectionPostProcessor,
        SearchResultApplierFactory $searchResultsApplierFactory,
        ProductCollectionSearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->collectionPreProcessor = $collectionPreProcessor;
        $this->collectionPostProcessor = $collectionPostProcessor;
        $this->searchResultApplierFactory = $searchResultsApplierFactory;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * Plugin to set the correct total count of search results being returned from a GraphQL query
     * search.
     *
     * @param \Magento\CatalogGraphQl\Model\Resolver\Products\DataProvider\ProductSearch $subject
     * @param SearchResultsInterface $result
     * @param SearchCriteriaInterface $searchCriteria
     * @param SearchResultInterface $searchResult
     * @param array $attributes
     * @param ContextInterface|null $context
     * @return SearchResultsInterface
     * @throws \Magento\Framework\Exception\InputException
     */
    public function afterGetList(
        \Magento\CatalogGraphQl\Model\Resolver\Products\DataProvider\ProductSearch $subject,
        SearchResultsInterface $result,
        SearchCriteriaInterface $searchCriteria,
        SearchResultInterface $searchResult,
        array $attributes = [],
        ContextInterface $context = null
    ): SearchResultsInterface {

        $collection = $this->collectionFactory->create();

        //Create a copy of search criteria without filters to preserve the results from search
        $searchCriteriaForCollection = $this->searchCriteriaBuilder->build($searchCriteria);
        //Apply CatalogSearch results from search and join table
        $this->getSearchResultsApplier(
            $searchResult,
            $collection,
            $this->getSortOrderArray($searchCriteriaForCollection)
        )->apply();

        $this->collectionPreProcessor->process($collection, $searchCriteriaForCollection, $attributes, $context);
        $collection->load();
        $this->collectionPostProcessor->process($collection, $attributes);

        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($searchCriteriaForCollection);
        $searchResults->setItems($collection->getItems());
        $searchResults->setTotalCount($collection->getSize());
        return $searchResults;
    }

    /**
     * Create searchResultApplier
     *
     * @param SearchResultInterface $searchResult
     * @param Collection $collection
     * @param array $orders
     * @return SearchResultApplierInterface
     */
    private function getSearchResultsApplier(
        SearchResultInterface $searchResult,
        Collection $collection,
        array $orders
    ): SearchResultApplierInterface {
        return $this->searchResultApplierFactory->create(
            [
                'collection' => $collection,
                'searchResult' => $searchResult,
                'orders' => $orders
            ]
        );
    }

    /**
     * Format sort orders into associative array
     *
     * E.g. ['field1' => 'DESC', 'field2' => 'ASC", ...]
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return array
     * @throws \Magento\Framework\Exception\InputException
     */
    private function getSortOrderArray(SearchCriteriaInterface $searchCriteria): array
    {
        $ordersArray = [];
        $sortOrders = $searchCriteria->getSortOrders();
        if (is_array($sortOrders)) {
            foreach ($sortOrders as $sortOrder) {
                // I am replacing _id with entity_id because in ElasticSearch _id is required for sorting by ID.
                // Where as entity_id is required when using ID as the sort in $collection->load();.
                // @see \Magento\CatalogGraphQl\Model\Resolver\Products\Query\Search::getResult
                if ($sortOrder->getField() === '_id') {
                    $sortOrder->setField('entity_id');
                }
                $ordersArray[$sortOrder->getField()] = $sortOrder->getDirection();
            }
        }

        return $ordersArray;
    }
}
