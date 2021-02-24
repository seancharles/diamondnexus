<?php

namespace ForeverCompanies\StonesIntermediary\Plugin;

use ForeverCompanies\StonesIntermediary\Ui\DataProvider\StonesSupplier\ListingDataProvider as SupplierDataProvider;
use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult;

class AddAttributesToUiDataProvider
{
    /** @var AttributeRepositoryInterface */
    private $attributeRepository;

    /** @var ProductMetadataInterface */
    private $productMetadata;

    /**
     * Constructor
     *
     * @param AttributeRepositoryInterface $attributeRepository
     * @param ProductMetadataInterface $productMetadata
     */
    public function __construct(
        AttributeRepositoryInterface $attributeRepository,
        ProductMetadataInterface $productMetadata
    ) {
        $this->attributeRepository = $attributeRepository;
        $this->productMetadata = $productMetadata;
    }

    /**
     * Get Search Result after plugin
     *
     * @param SupplierDataProvider $subject
     * @param SearchResult $result
     * @return SearchResult
     * @throws NoSuchEntityException
     */
    public function afterGetSearchResult(SupplierDataProvider $subject, SearchResult $result)
    {
        if ($result->isLoaded()) {
            return $result;
        }

        $column = 'id';

        $attribute = $this->attributeRepository->get('catalog_category', 'name');

        /*$result->getSelect()->joinLeft(
            ['devgridname' => $attribute->getBackendTable()],
            'devgridname.' . $column . ' = main_table.' . $column . ' AND devgridname.attribute_id = '
            . $attribute->getAttributeId(),
            ['name' => 'devgridname.value']
        );

        $result->getSelect()->where('devgridname.value LIKE "B%"');*/

        return $result;
    }
}
