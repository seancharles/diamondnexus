<?php
 
namespace ForeverCompanies\DynamicBundle\Model\Product;
 
class Type extends \Magento\Catalog\Model\Product\Type\AbstractType
{
    const TYPE_ID = 'dynamic';

    /**
     * {@inheritdoc}
     */
    public function deleteTypeSpecificData(\Magento\Catalog\Model\Product $product)
    {
        // method intentionally empty
    }
	
	public function getEditableAttributes($product)
	{
		$attributes = parent::getEditableAttributes($product);
		
		unset($attributes);
		
		return $attributes;
	}
}