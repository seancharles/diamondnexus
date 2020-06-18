<?php
 
namespace ForeverCompanies\DynamicBundle\Model\Product\Type;
 
class Dynamic extends \Magento\Bundle\Model\Product\Type
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
		
		unset($attributes['quantity']);
		
		return $attributes;
	}
}