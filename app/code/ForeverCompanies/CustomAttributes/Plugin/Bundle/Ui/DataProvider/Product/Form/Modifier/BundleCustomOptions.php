<?php

namespace ForeverCompanies\CustomAttributes\Plugin\Bundle\Ui\DataProvider\Product\Form\Modifier;

use ForeverCompanies\CustomAttributes\Model\Config\Source\Product\BundleCustomizationType;
use Magento\Ui\Component\Form\Field;
use Magento\Ui\Component\Form\Element\Select;
use Magento\Ui\Component\Form\Element\DataType\Text;

class BundleCustomOptions
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
     * @param BundlePanel $subject
     * @param $meta
     * @return mixed
     */
    public function afterModifyMeta(
        BundlePanel $subject,
        $meta
    ) {
        $meta['bundle-items']['children']['bundle_options']['children']['record']['children']
        ['product_bundle_container']['children']['option_info']['children']['bundle_customization_type'] =
            $this->getCustomizationTypeFieldConfig(
                25
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
