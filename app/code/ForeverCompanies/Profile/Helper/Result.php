<?php

namespace ForeverCompanies\Profile\Helper;
 
class Result
{
	CONST ERROR_TYPE_FORM_KEY = 'form_key';
	CONST ERROR_TYPE_PRODUCT = 'product';
	CONST ERROR_TYPE_CUSTOM_OPTION_VALIDATION = 'custom_option';
	CONST ERROR_TYPE_BUNDLE_OPTION_VALIDATION = 'bundle_option';
	CONST ERROR_TYPE_CONFIGURABLE_OPTION_VALIDATION = 'configurable_option';
	CONST ERROR_TYPE_EXCEPTION = 'general_exception';
	
	protected $productloader;
	
	protected $errors = array();
	protected $success = false;
	
	// this is a singular response message
	protected $message = '';
	protected $profile = '';
	
	
	public function __construct(
		\Magento\Catalog\Model\ProductFactory $productloader
	) {
		$this->productloader = $productloader;
	}
	
	// validate add to cart params for simple and configurables
	public function validateBundleProductOptions($product, $customOptions, $bundleSelections, $bundleCustomOptions, $dynamicId)
	{
		$acustomOptions = array();
		$aOptionsResult = array();
		$aBundleSelections = array();
		
		foreach($customOptions as $option) {
			$acustomOptions[$option->id] = $option->value;
		}
		
		foreach($bundleSelections as $selection) {
			$aBundleSelections[$selection->id] = $selection->value;
		}

		if($product->hasOptions() == true)
		{
			// get custom options
			$productOptions = $product->getOptions();

			foreach($productOptions as $option)
			{
				$aOptionsResult[$option->getId()] = "Invalid option selected for " . $option->getTitle();
				
				$values = $option->getValues();
				
				foreach($values as $valueId => $value)
				{
					// compare the option values to the selections
					if(isset($acustomOptions[$option->getOptionId()]) == true && $acustomOptions[$option->getOptionId()] == $valueId)
					{
						// remove the option from the array since it has a valid selection
						unset($aOptionsResult[$option->getOptionId()]);
					}
				}
			}
			
			// get bundled options
			$optionsCollection = $product->getTypeInstance(true)
				->getOptionsCollection($product);

			foreach ($optionsCollection as $option){
				if($option->getIsDynamicSelection() == 1){
					
					// additional dynamic bundle validation goes here to
					// make sure stone and setting will be compatible
					//echo $option->getTitle() . " is dynamic\n";
				}
				
				// handle native bundle
				$selections = $product->getTypeInstance(true)
					->getSelectionsCollection($option->getOptionId(),$product);
				
				foreach( $selections as $selection )
				{
					$childId = $selection->getProductId();
				
					$childModel = $this->productloader->create()->load($childId);
					
					// only validate child custom options if the child product was selected as a bundled option
					if($childModel->hasOptions() == true && isset($aBundleSelections[$selection->getSelectionId()]) == true)
					{
						// get custom options
						$childProductOptions = $childModel->getOptions();

						foreach($childProductOptions as $option)
						{
							$optionsResult[$selection->getSelectionId()][$childId][$option->getId()] = "Invalid option selected for " . $option->getTitle();
							
							$values = $option->getValues();
							
							foreach($values as $valueId => $value)
							{
								// compare the option values to the selections
								if(
									isset($bundleCustomOptions[$selection->getSelectionId()][$childId][$option->getOptionId()]) == true &&
									$bundleCustomOptions[$selection->getSelectionId()][$childId][$option->getOptionId()] == $valueId
								)
								{
									// remove the option from the array since it has a valid selection
									unset($optionsResult[$selection->getSelectionId()][$childId][$option->getOptionId()]);
								}
							}
						}
					}
				}
			}
		}
		
		if(count($aOptionsResult) > 0) {
			return $aOptionsResult;
		} else {
			return false;
		}
	}
	
	// validate add to cart params for simple and configurables
	public function validateProductOptions($product, $params)
	{
		$optionsResult = array();
		
		if($product->hasOptions() == true)
		{
			// get custom options
			$productOptions = $product->getOptions();

			foreach($productOptions as $option)
			{
				// create a temporary key for this option, we will remove if it
				// a valid option is passed otherwise this will be returned to
				// help identity the error
				
				$optionsResult[$option['option_id']] = "Invalid option selected for " . $option->getTitle();
				
				$values = $option->getValues();
				
				foreach($values as $valueId => $value)
				{
					// compare the option values to the selections
					if(isset($params['options'][$option['option_id']]) == true && $params['options'][$option['option_id']] == $valueId)
					{
						// remove the option from the array since it has a valid selection
						unset($optionsResult[$option['option_id']]);
					}
				}
			}
			
			if($product->getTypeId() == 'configurable') {
				// pull configurable options
				$configOptions = $product->getTypeInstance()->getConfigurableOptions($product);
				
				foreach($configOptions as $optionId => $config)
				{
					$optionsResult[$optionId] = "Invalid option for attribute " . $config[0]['super_attribute_label'];
					
					foreach($config as $option){
						if($params['super_attribute'][$optionId] == $option['value_index']) {
							unset($optionsResult[$optionId]);
						}
					}
				}
				
				// loop through the configurable attributes on the product to get the option title
				// foreach ($product->getTypeInstance()->getConfigurableAttributes($product) as $attribute) {
				//	if(isset($optionsResult[$attribute->getId()]) == true) {
				//		$optionsResult[$attribute->getId()] = "Invalid option selected for " . $attribute->getLabel();
				//	}
				// }
			}
		}
		
		if(count($optionsResult) > 0) {
			return $optionsResult;
		} else {
			return false;
		}
	}
	
	public function getResult() {
		print_r(json_encode([
			'success' => $this->success,
			'errors' => $this->errors,
			'message' => $this->message,
			'profile' => $this->profile
		]));
	}

	public function setProfile($profile) {
		$this->profile = $profile;
	}
	
	public function getProfile() {
		return $this->profile;
	}

	public function setSuccess($bool, $message = null) {
		if($message != null) {
			$this->message = $message;
		}
		$this->success = (bool) $bool;
	}

	public function getSuccess() {
		return $this->success;
	}
	
	public function getMessage() {
		return $this->message;
	}
	
	public function getErrors() {
		return $this->errors;
	}

	public function addCartError($message)
	{
		$this->errors[] = [
			"type" => self::ERROR_TYPE_CART,
			"message" => $message
		];
	}

	public function addFormKeyError()
	{
		$this->errors[] = [
			"type" => self::ERROR_TYPE_FORM_KEY,
			"message" => "Invalid form key."
		];
	}

	public function addProductError($productId = 0, $message = null)
	{
		$this->success = false;
		$this->errors[] = [
			"type" => self::ERROR_TYPE_PRODUCT,
			"message" => $message,
			"product_id" => $productId
		];
	}
	
	public function addCustomOptionError($productId = 0)
	{
		$this->success = false;
		$this->errors[] = [
			"type" => self::ERROR_TYPE_CUSTOM_OPTION_VALIDATION,
			"message" => "Please enter a valid custom option",
			"product_id" => $productId
		];
	}

	public function addBundleOptionError($productId = 0, $selectionId = 0, $optionId = 0)
	{
		$this->success = false;
		$this->errors[] = [
			"type" => self::ERROR_TYPE_BUNDLE_OPTION_VALIDATION,
			"message" => "Please enter a valid custom option",
			"product_id" => $productId
		];
	}
	
	public function addConfigurableOptionError($productId = 0, $optionId = 0)
	{
		$this->success = false;
		$this->errors[] = [
			"type" => self::ERROR_TYPE_CONFIGURABLE_OPTION_VALIDATION,
			"message" => "Please enter a valid custom option",
			"product_id" => $productId
		];
	}
	
	public function addExceptionError($e)
	{
		$this->success = false;
		$this->errors[] = [
			"type" => self::ERROR_TYPE_EXCEPTION,
			"message" => $e->getMessage()
		];
	}
}