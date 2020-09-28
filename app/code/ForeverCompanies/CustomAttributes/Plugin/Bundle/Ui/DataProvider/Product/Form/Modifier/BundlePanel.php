<?php

namespace ForeverCompanies\CustomAttributes\Plugin\Bundle\Ui\DataProvider\Product\Form\Modifier;

use ForeverCompanies\CustomAttributes\Model\Config\Source\Product\BundleCustomizationType;
use Magento\Ui\Component\Form\Field;
use Magento\Ui\Component\Form\Element\Select;
use Magento\Ui\Component\Form\Element\DataType\Text;
use Magento\Ui\Component\Form;

class BundlePanel
{
    const FIELD_CUSTOMIZATION_TYPE = 'bundle_customization_type';

    /**
     * @var BundleCustomizationType
     */
    protected $customizationType;

    /**
     * BundleCustomOptions constructor.
     * @param BundleCustomizationType $customizationType
     */
    public function __construct(
        BundleCustomizationType $customizationType
    ) {
        $this->customizationType = $customizationType;
    }

    /**
     * @param \Magento\Bundle\Ui\DataProvider\Product\Form\Modifier\BundlePanel $subject
     * @param $meta
     * @return mixed
     */
    public function afterModifyMeta(
        \Magento\Bundle\Ui\DataProvider\Product\Form\Modifier\BundlePanel $subject,
        $meta
    ) {
        $meta['bundle-items']['children']['bundle_options']['children']['record']['children']
        ['product_bundle_container']['children']['option_info']['children'][self::FIELD_CUSTOMIZATION_TYPE] =
            $this->getCustomizationTypeFieldConfig(
                25
            );
        $fieldSet = [
            'is_dynamic_selection' => [
                'dataType' => Form\Element\DataType\Text::NAME,
                'formElement' => Form\Element\Select::NAME,
                'label' => 'Dynamic Option',
                'dataScope' => 'is_dynamic_selection',
                'sortOrder' => 40
            ]
        ];

        foreach ($fieldSet as $filed => $fieldOptions) {
            $meta['bundle-items']['children']['bundle_options']['children']
            ['record']['children']['product_bundle_container']['children']['option_info']['children'][$filed] =
                $this->getSelectionCustomText($fieldOptions);
        }
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
                            'component' => 'Magento_Ui/js/form/element/select',
                            'formElement' => Select::NAME,
                            'parentContainer' => 'product_bundle_container',
                            'selections' => 'bundle_selections',
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
                        'dataType' => $fieldOptions['dataType'],
                        'formElement' => $fieldOptions['formElement'],
                        'label' => __($fieldOptions['label']),
                        'dataScope' => $fieldOptions['dataScope'],
                        'sortOrder' => $fieldOptions['sortOrder'],
                        'options' => [
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
