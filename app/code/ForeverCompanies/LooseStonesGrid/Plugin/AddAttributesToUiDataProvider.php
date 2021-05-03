<?php

namespace ForeverCompanies\LooseStonesGrid\Plugin;

use ForeverCompanies\LooseStonesGrid\Ui\DataProvider\Product\ListingDataProvider as ProductDataProvider;
use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Framework\App\ProductMetadataInterface;
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
     * @param \Magento\Eav\Api\AttributeRepositoryInterface $attributeRepository
     * @param \Magento\Framework\App\ProductMetadataInterface $productMetadata
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
     * @param \Dev\Grid\Ui\DataProvider\Category\ListingDataProvider $subject
     * @param \Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult $result
     * @return \Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult
     */
    public function afterGetSearchResult(ProductDataProvider $subject, SearchResult $result)
    {
        if ($result->isLoaded()) {
            return $result;
        }
        
        $edition = $this->productMetadata->getEdition();
        
        $column = 'entity_id';
        
        if ($edition == 'Enterprise') {
            $column = 'row_id';
        }
        
        $attribute = $this->attributeRepository->get('catalog_product', 'name');
        
        $result->getSelect()->joinLeft(
            ['devgridname' => $attribute->getBackendTable()],
            'devgridname.' . $column . ' = main_table.' . $column . ' AND devgridname.attribute_id = '
            . $attribute->getAttributeId(),
            ['name' => 'devgridname.value']
            );
        
        $result->getSelect()->where('devgridname.value LIKE "B%"');
        
        return $result;
    }
}
