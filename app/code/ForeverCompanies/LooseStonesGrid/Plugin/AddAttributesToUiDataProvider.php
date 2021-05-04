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
        
        $attributeArr = array(
            "supplier",
            "filter_ship_date",
            "rapaport",
            "pct_off_rap",
            
            "price",
            // "custom_price",
            
            "cost",
            "custom_cost",
            
            "cert_url_key",
            "diamond_img_url",
            "video_url",
            "online",
            "lab",
            "shape",
            "color",
            "clarity",
            "cut_grade",
            "stone_carat",
            "country_of_manufacture",
            "length_to_width",
            
            "measurements",
            "polish",
            "symmetry",
            "girdle",
            "fluor",
            "as_grown",
            "born_on_date",
            "carbon_neutral",
            "blockchain_verified",
            "charitable_contribution",
            "cvd",
            "hpht",
            "patented",
            "custom",
            "color_of_colored_diamonds",
            "hue",
            "intensity"
        );
        
        foreach ($attributeArr as $attr) {
            try {
                $attribute = $this->attributeRepository->get('catalog_product', $attr);
            } catch(\Magento\Framework\Exception\NoSuchEntityException $e) {
                
            }
            
            if (isset($attribute)) {
                $result->getSelect()->joinLeft(
                    ['stonesgrid_' . $attr => $attribute->getBackendTable()],
                    'stonesgrid_' . $attr . '.' . $column . ' = main_table.' . $column . ' AND stonesgrid_' . $attr . '.attribute_id = '
                    . $attribute->getAttributeId(),
                    [$attr => 'stonesgrid_' . $attr . '.value']
                );
            }
            unset($attribute);
        }
        
        
        /*
        $attribute = $this->attributeRepository->get('catalog_product', 'supplier');
        
        $result->getSelect()->joinLeft(
            ['stonesgrid_supplier' => $attribute->getBackendTable()],
            'stonesgrid_supplier.' . $column . ' = main_table.' . $column . ' AND stonesgrid_supplier.attribute_id = '
            . $attribute->getAttributeId(),
            ['supplier' => 'stonesgrid_supplier.value']
        );
        */
        
    
    //   $result->getSelect()->where('devgridname.value LIKE "B%"');
        
        return $result;
    }
}
