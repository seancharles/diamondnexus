<?php

use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;
use Magento\Ui\Component\Form\Field;
use Magento\Ui\Component\Form\Fieldset;

class Example extends AbstractModifier
{
    /**
     * @param array $meta
     *
     * @return array
     */
    public function modifyMeta(array $meta): array
    {
    }
    
    /**
     * {@inheritdoc}
     */
    public function modifyData(array $data)
    {
        return $data;
    }
}
