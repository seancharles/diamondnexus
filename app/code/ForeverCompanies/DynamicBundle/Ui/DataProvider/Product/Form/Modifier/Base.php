<?php
namespace ForeverCompanies\DynamicBundle\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;
use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\CustomOptions;
use Magento\Ui\Component\Form\Element\Input;
use Magento\Ui\Component\Form\Element\Checkbox;
use Magento\Ui\Component\Form\Element\DataType\Text;
use Magento\Ui\Component\Form\Field;

class Base extends AbstractModifier
{
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
       $fields['is_special_offer'] = $this->getSpecialOfferFieldConfig();

       return $fields;
   }

   /**
    * The custom option fields config
    *
    * @return array
    */
   protected function getValueFieldsConfig()
   {
       $fields['description'] = $this->getDescriptionFieldConfig();

       return $fields;
   }

   /**
    * Get special offer field config
    *
    * @return array
    */
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

   /**
    * Get description field config
    *
    * @return array
    */
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
}
