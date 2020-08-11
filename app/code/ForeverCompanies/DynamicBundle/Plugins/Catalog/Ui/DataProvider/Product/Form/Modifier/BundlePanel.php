<?php

namespace ForeverCompanies\DynamicBundle\Plugins\Catalog\Ui\DataProvider\Product\Form\Modifier;

use Magento\Ui\Component\Form;;

class BundlePanel
{
    /**
     * @param \Magento\Bundle\Ui\DataProvider\Product\Form\Modifier\BundlePanel $subject
     * @param $meta
     * @return mixed
     */
    public function afterModifyMeta(\Magento\Bundle\Ui\DataProvider\Product\Form\Modifier\BundlePanel $subject, $meta)
    {
        $fieldSet = [
            'is_dynamic_selection' => [
                'dataType' => Form\Element\DataType\Text::NAME,
                'formElement'   => Form\Element\Select::NAME,
                'label' => 'Dynamic Option',
                'dataScope' => 'is_dynamic_selection',
                'sortOrder' => 40
            ]
        ];

        foreach ($fieldSet as $filed => $fieldOptions)
        {
            $meta['bundle-items']['children']['bundle_options']['children']
            ['record']['children']['product_bundle_container']['children']['option_info']['children'][$filed] = $this->getSelectionCustomText($fieldOptions);
        }

        return $meta;
    }

    /**
     * @param $fieldOptions
     * @return array
     */
    protected function getSelectionCustomText($fieldOptions)
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'componentType' => Form\Field::NAME,
                        'dataType'      => $fieldOptions['dataType'],
                        'formElement'   => $fieldOptions['formElement'],
                        'label'         => __($fieldOptions['label']),
                        'dataScope'     => $fieldOptions['dataScope'],
                        'sortOrder'     => $fieldOptions['sortOrder'],
                        'options'       => [
                            [
                                'label' => __('No'),
                                'value' => '0',
                            ],
                            [
                                'label' => __('Yes'),
                                'value' => '1',
                            ]
                        ]
                    ]
                ]
            ]
        ];
    }
}