<?php

namespace ForeverCompanies\LooseStonesGrid\Plugin;

use ForeverCompanies\LooseStonesGrid\Ui\DataProvider\Product\ListingDataProvider as ProductDataProvider;
use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult;

class AddAttributesToUiDataProvider
{
    private $attributeRepository;
    private $productMetadata;
    
    public function __construct(
        AttributeRepositoryInterface $attributeRepository,
        ProductMetadataInterface $productMetadata
        ) {
            $this->attributeRepository = $attributeRepository;
            $this->productMetadata = $productMetadata;
    }
    
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
            "stone_import_custom_price",
            "cost",
            "stone_import_custom_cost",
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
            "origin",
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
        return $result;
    }
}
