<?php
 
namespace ForeverCompanies\DynamicBundle\Model\Product\Type;
 
class Bundle extends \Magento\Bundle\Model\Product\Type
{
    const TYPE_ID = 'dynamic_bundle';

    /**
     * {@inheritdoc}
     */
    public function deleteTypeSpecificData(\Magento\Catalog\Model\Product $product)
    {
        // method intentionally empty
    }
}