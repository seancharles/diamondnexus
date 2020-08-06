<?php
namespace ForeverCompanies\DynamicBundle\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;
use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\CustomOptions;
use Magento\Ui\Component\Form\Element\Input;
use Magento\Ui\Component\Form\Element\Checkbox;
use Magento\Ui\Component\Form\Element\DataType\Text;
use Magento\Ui\Component\Form\Element\Select;
use Magento\Ui\Component\Form\Element\DataType\Number;
use Magento\Ui\Component\Form\Field;

class Base extends AbstractModifier
{
	protected $eavConfig;
	
	public function __construct(
		\Magento\Eav\Model\Config $eavConfig
	) {
        $this->eavConfig = $eavConfig;
	}
	
   /**
    * @var array
    */
   protected $meta = [];

   /**
    * {@inheritdoc}
    */
   public function modifyData(array $data)
   {
       return $data;
   }

   /**
    * {@inheritdoc}
    */
   public function modifyMeta(array $meta)
   {
       $this->meta = $meta;

       $this->addFields();

       return $this->meta;
   }

   /**
    * Adds fields to the meta-data
    */
   protected function addFields()
   {
       $groupCustomOptionsName    = CustomOptions::GROUP_CUSTOM_OPTIONS_NAME;
       $optionContainerName       = CustomOptions::CONTAINER_OPTION;
       $commonOptionContainerName = CustomOptions::CONTAINER_COMMON_NAME;

       // Add fields to the option
       $this->meta[$groupCustomOptionsName]['children']['options']['children']['record']['children']
       [$optionContainerName]['children'][$commonOptionContainerName]['children'] = array_replace_recursive(
           $this->meta[$groupCustomOptionsName]['children']['options']['children']['record']['children']
           [$optionContainerName]['children'][$commonOptionContainerName]['children'],
           $this->getOptionFieldsConfig()
       );

       // Add fields to the values
       $this->meta[$groupCustomOptionsName]['children']['options']['children']['record']['children']
       [$optionContainerName]['children']['values']['children']['record']['children'] = array_replace_recursive(
           $this->meta[$groupCustomOptionsName]['children']['options']['children']['record']['children']
           [$optionContainerName]['children']['values']['children']['record']['children'],
           $this->getValueFieldsConfig()
       );
   }

   /**
    * The custom option fields config
    *
    * @return array
    */
   protected function getOptionFieldsConfig()
   {
       # $fields['is_special_offer'] = $this->getSpecialOfferFieldConfig();
	   
	   # replace this with above if additional parameter is needed
	   $fields = array();

       return $fields;
   }

   /**
    * The custom option fields config
    *
    * @return array
    */
   protected function getValueFieldsConfig()
   {
		$fields['shippinggroup'] = $this->getShippingGroupFieldConfig();

		return $fields;
   }

   /**
    * Get special offer field config
    *
    * @return array
    */
	/*
   protected function getSpecialOfferFieldConfig()
   {
       return [
           'arguments' => [
               'data' => [
                   'config' => [
                       'label'         => __('Is Special Offer'),
                       'componentType' => Field::NAME,
                       'formElement'   => Checkbox::NAME,
                       'dataScope'     => 'is_special_offer',
                       'dataType'      => Text::NAME,
                       'sortOrder'     => 65,
                       'valueMap'      => [
                           'true'  => '1',
                           'false' => '0'
                       ],
                   ],
               ],
           ],
       ];
   }
   */

   /**
    * Get description field config
    *
    * @return array
    */
	/*
   protected function getDescriptionFieldConfig()
   {
       return [
           'arguments' => [
               'data' => [
                   'config' => [
                       'label' => __('Description'),
                       'componentType' => Field::NAME,
                       'formElement'   => Input::NAME,
                       'dataType'      => Text::NAME,
                       'dataScope'     => 'description',
                       'sortOrder'     => 41
                   ],
               ],
           ],
       ];
   }
   */
   
   /**
    * Get shipping group field config
    *
    * @return array
    */
   protected function getShippingGroupFieldConfig()
   {
		// pull attribute options from EAV
		$attribute = $this->eavConfig->getAttribute('catalog_product', 'shipperhq_shipping_group');

		$options = $attribute->getSource()->getAllOptions();

		$arr = [];
		foreach ($options as $option) {
				$arr[] = $option;
		}
	   
		return [
			'arguments' => [
				'data' => [
					'config' => [
						'label' => __('Shipping Group Override'),
						'componentType' => Field::NAME,
						'formElement'   => Select::NAME,
						'dataType'      => Text::NAME,
						'dataScope'     => 'shippinggroup',
						'sortOrder'     => 42,
						'options'       => $arr
					],
				],
			],
       ];
   }
}
