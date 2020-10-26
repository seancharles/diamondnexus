<?php

namespace ForeverCompanies\DynamicBundle\Plugins\Ui\DataProvider\Product\Form\Modifier;

use Magento\Ui\Component\Form\Field;
use Magento\Ui\Component\Form\Element\Input;
use Magento\Ui\Component\Form\Element\Select;
use Magento\Ui\Component\Form\Element\DataType\Text;
use Magento\Bundle\Ui\DataProvider\Product\Form\Modifier\BundleCustomOptions as MagentoBundleCustomOptions;

class BundleCustomOptions
{

    // ...

    const FIELD_CUSTOM_FIELD_OPTION_NAME = 'option_sku';

    public function afterModifyMeta(MagentoBundleCustomOptions $subject, array $meta)
    {
        if (isset($meta['bundle-items']['children']['bundle_options']['children']['record']['children']['product_bundle_container']['children']['bundle_selections']['children']['record']['children'])) {


            $meta['bundle-items']['children']['bundle_options']['children']['record']['children']['product_bundle_container']['children']['bundle_selections']['children']['record']['children'][static::FIELD_CUSTOM_FIELD_OPTION_NAME] = $this->getCuststomFieldOptionFieldConfig(125);


            // Reorder table headings

            $action_delete = $meta['bundle-items']['children']['bundle_options']['children']['record']['children']['product_bundle_container']['children']['bundle_selections']['children']['record']['children']['action_delete'];
            unset($meta['bundle-items']['children']['bundle_options']['children']['record']['children']['product_bundle_container']['children']['bundle_selections']['children']['record']['children']['action_delete']);
            $meta['bundle-items']['children']['bundle_options']['children']['record']['children']['product_bundle_container']['children']['bundle_selections']['children']['record']['children']['action_delete'] = $action_delete;

            // There should be more convenient way to reorder table headings

        }

        return $meta;
    }

    protected function getCuststomFieldOptionFieldConfig($sortOrder)
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'label' => __('Sku Override'),
                        'componentType' => Field::NAME,
                        'formElement' => Input::NAME,
                        'dataScope' => static::FIELD_CUSTOM_FIELD_OPTION_NAME,
                        'dataType' => Text::NAME,
                        'sortOrder' => $sortOrder,
                        'default' => '',
                        /*
                        'options' => [
                            [
                                'label' => __('Option 1'),
                                'value' => 'option_1',
                            ],
                            [
                                'label' => __('Option 2'),
                                'value' => 'option_2',
                            ],
                            [
                                'label' => __('Option 3'),
                                'value' => 'option_3',
                            ],
                        ],
                        */
                    ],
                ],
            ],
        ];
    }

    // ...
}
