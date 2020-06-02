<?php
 
namespace ForeverCompanies\BundleOptions\Model\Product\Type;
 
class Bundle extends \Magento\Bundle\Model\Product\Type
{
    const TYPE_ID = 'forevercompanies_bundleoptions_type_code';

    /**
     * {@inheritdoc}
     */
    public function deleteTypeSpecificData(\Magento\Catalog\Model\Product $product)
    {
        // method intentionally empty
    }
}