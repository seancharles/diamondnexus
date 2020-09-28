<?php

namespace ForeverCompanies\DynamicBundle\Model\Product\Type;

class Dynamic extends \Magento\Bundle\Model\Product\Type
{
    const TYPE_ID = 'dynamic';

    public function getEditableAttributes($product)
    {
        $attributes = parent::getEditableAttributes($product);

        unset($attributes['quantity']);

        return $attributes;
    }
}
