<?php

namespace ForeverCompanies\CustomAttributes\Plugin\Catalog\Ui\DataProvider\Product\Form\Modifier;

use ForeverCompanies\CustomAttributes\Model\Config\Source\Product\CustomizationType;
use Magento\Ui\Component\Form\Field;
use Magento\Ui\Component\Form\Element\Select;
use Magento\Ui\Component\Form\Element\DataType\Text;

class CustomOptions
{
    const FIELD_CUSTOMIZATION_TYPE = 'customization_type';

    /**
     * @var CustomizationType
     */
    protected $customizationType;

    /**
     * CustomOptions constructor.
     * @param CustomizationType $customizationType
     */
    public function __construct(
        CustomizationType $customizationType
    )
    {
        $this->customizationType = $customizationType;
    }

    /**
     * @param \Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\CustomOptions $subject
     * @param $meta
     * @return mixed
     */
    public function afterModifyMeta(
        \Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\CustomOptions $subject,
        $meta
    )
    {
        $meta['custom_options']['children']['options']['children']['record']['children']['container_option']
        ['children']['container_common']['children']['customization_type'] =
            $this->getCustomizationTypeFieldConfig(
                35
            );
        return $meta;
    }

    /**
     *
     * @param $sortOrder
     * @param array $options
     * @return array
     */
    protected function getCustomizationTypeFieldConfig($sortOrder, array $options = [])
    {
        return array_replace_recursive(
            [
                'arguments' => [
                    'data' => [
                        'config' => [
                            'label' => __('Customization Type'),
                            'componentType' => Field::NAME,
                            'formElement' => Select::NAME,
                            'dataScope' => static::FIELD_CUSTOMIZATION_TYPE,
                            'dataType' => Text::NAME,
                            'sortOrder' => $sortOrder,
                            'options' => $this->customizationType->toOptionArray(),
                        ],
                    ],
                ],
            ],
            $options
        );
    }
}
