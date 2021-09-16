<?php

namespace ForeverCompanies\Profile\Helper;
 
class Result
{
    const PRODUCT_CUSTOM_OPTION = 'custom_option';
    const PRODUCT_BUNDLE_OPTION = 'bundle_option';
    const PRODUCT_BUNDLE_CHILD_CUSTOM_OPTION = 'bundle_child_custom_option';
    const PRODUCT_CONFIGURABLE_OPTION = 'configurable_option';

    const ERROR_TYPE_PRODUCT = 'product';
    const ERROR_TYPE_FORM_KEY = 'form_key';
    const ERROR_TYPE_EXCEPTION = 'general_exception';
    
    protected $productloader;
    
    protected $errors = [
        self::PRODUCT_CUSTOM_OPTION => [],
        self::PRODUCT_BUNDLE_OPTION => [],
        self::PRODUCT_BUNDLE_CHILD_CUSTOM_OPTION => [],
        self::PRODUCT_CONFIGURABLE_OPTION => []
    ];
    
    protected $success = false;
    
    // this is a singular response message
    protected $message = '';
    protected $profile = '';
    
    public function __construct(
        \Magento\Catalog\Model\ProductFactory $productloader
    ) {
        $this->productloader = $productloader;
    }
    
    private function keyFormat($params)
    {
        $buffer = $params[0];
        
        for ($i=1; $i<count($params); $i++) {
            $buffer = $params[$i] . '_' . $buffer;
        }
        
        return $buffer;
    }
    
    private function hasOptionErrors()
    {
        $hasErrors = false;
        
        if (count($this->errors[self::PRODUCT_CUSTOM_OPTION]) > 0 ||
            count($this->errors[self::PRODUCT_BUNDLE_OPTION]) > 0 ||
            count($this->errors[self::PRODUCT_BUNDLE_CHILD_CUSTOM_OPTION]) > 0 ||
            count($this->errors[self::PRODUCT_CONFIGURABLE_OPTION]) > 0
        ) {
            $hasErrors = true;
        }
        
        return $hasErrors;
    }
    
    // validate add to cart params for simple and configurables
    public function validateBundleProductOptions(
        $product,
        $customOptions,
        $bundleSelections,
        $bundleCustomOptions,
        $dynamicId
    ) {
        $aCustomOptions = [];
        $aBundleSelections = [];
        
        foreach ($customOptions as $option) {
            $aCustomOptions[$option->id] = $option->value;
        }
        
        foreach ($bundleSelections as $selection) {
            $aBundleSelections[$selection->id] = $selection->value;
        }

        if ($product->hasOptions() == true) {
            // get custom options
            $productOptions = $product->getOptions();

            foreach ($productOptions as $option) {
                $this->errors[self::PRODUCT_CUSTOM_OPTION][$option->getOptionId()] = [
                    "message" => "Invalid custom option selected for " . $option->getTitle(),
                    "option_id" => $option->getOptionId()
                ];
                
                $values = $option->getValues();
                
                foreach ($values as $valueId => $value) {
                    // compare the option values to the selections
                    if (isset($aCustomOptions[$option->getOptionId()])
                        == true && $aCustomOptions[$option->getOptionId()] == $valueId
                    ) {
                        // remove the option from the array since it has a valid selection
                        unset($this->errors[self::PRODUCT_CUSTOM_OPTION][$option->getOptionId()]);
                    }
                }
            }
            
            // get bundled options from bundle
            $optionsCollection = $product->getTypeInstance(true)
                ->getOptionsCollection($product);

            foreach ($optionsCollection as $bundleOption) {
                if ($option->getIsDynamicSelection() == 1) {
                    
                    // additional dynamic bundle validation goes here to
                    // make sure stone and setting will be compatible
                    //echo $option->getTitle() . " is dynamic\n";
                }
                
                $this->errors[self::PRODUCT_BUNDLE_OPTION][$bundleOption->getOptionId()] = [
                    "message" => "Invalid bundle option selected for " . $bundleOption->getTitle(),
                    "option_id" => $bundleOption->getOptionId()
                ];
                
                // handle native bundle
                $selections = $product->getTypeInstance(true)
                    ->getSelectionsCollection($bundleOption->getOptionId(), $product);
                
                foreach ($selections as $selection) {
                    $childId = $selection->getProductId();
                
                    $childModel = $this->productloader->create()->load($childId);
                    
                    if (isset(
                        $aBundleSelections[$bundleOption->getOptionId()]
                    ) == true && $aBundleSelections[$bundleOption->getOptionId()] == $selection->getSelectionId()) {
                        // remove error if valid selection is found
                        unset($this->errors[self::PRODUCT_BUNDLE_OPTION][$bundleOption->getOptionId()]);
                        
                    }
                    
                    // only validate child custom options if the child product was selected as a bundled option
                    if ($childModel->hasOptions() == true
                        && isset($aBundleSelections[$selection->getSelectionId()]) == true
                    ) {
                        // get custom options
                        $childProductOptions = $childModel->getOptions();

                        foreach ($childProductOptions as $chuldOption) {
                            $key = $this->keyFormat([$selection->getSelectionId(), $childId, $chuldOption->getId()]);
                            
                            $this->errors[self::PRODUCT_BUNDLE_CHILD_CUSTOM_OPTION][$key] = [
                                "message" => "Invalid option selected for child product " . $chuldOption->getTitle(),
                                "product_id" => $childModel->getId(),
                                "selection_id" => $selection->getSelectionId(),
                                "option_id" => $chuldOption->getId()
                            ];
                            
                            $values = $chuldOption->getValues();
                            
                            foreach ($values as $valueId => $value) {
                                // compare the option values to the selections
                                if (isset(
                                    $bundleCustomOptions[
                                        $selection->getSelectionId()
                                    ][$childId][$chuldOption->getOptionId()]
                                ) == true &&
                                    $bundleCustomOptions[
                                        $selection->getSelectionId()
                                    ][$childId][$chuldOption->getOptionId()] == $valueId
                                ) {
                                    // remove the option from the array since it has a valid selection
                                    unset($this->errors[self::PRODUCT_BUNDLE_CHILD_CUSTOM_OPTION][$key]);
                                }
                            }
                        }
                    }
                }
            }
        }
        
        if ($this->hasOptionErrors() == true) {
            
            // set the error message
            $this->setSuccess(false, "Product options are required.");
            
            return true;

        } else {
            return false;
        }
    }
    
    // validate add to cart params for simple and configurables
    public function validateProductOptions($product, $params)
    {
        if ($product->hasOptions() == true && $product->getAttributeSetId() != 31) {
            // get custom options
            $productOptions = $product->getOptions();

            foreach ($productOptions as $option) {
                // create a temporary key for this option, we will remove if it
                // a valid option is passed otherwise this will be returned to
                // help identity the error
                
                $this->errors[self::PRODUCT_CUSTOM_OPTION][$option['option_id']]
                    = "Invalid option selected for " . $option['title'];
                
                $values = $option->getValues();
                
                foreach ($values as $valueId => $value) {
                    // compare the option values to the selections
                    if (isset(
                        $params['options'][$option['option_id']]
                    ) == true
                        && $params['options'][$option['option_id']] == $valueId) {
                        // remove the option from the array since it has a valid selection
                        unset($this->errors[self::PRODUCT_CUSTOM_OPTION][$option['option_id']]);
                    }
                }
            }
            
            if ($product->getTypeId() == 'configurable') {
                // pull configurable options
                $configOptions = $product->getTypeInstance()->getConfigurableOptions($product);
                
                foreach ($configOptions as $optionId => $config) {
                    $this->errors[self::PRODUCT_CONFIGURABLE_OPTION][$optionId]
                    = "Invalid option for attribute " . $config[0]['super_attribute_label'];
                    
                    foreach ($config as $option) {
                        if (isset(
                            $params['super_attribute'][$optionId]
                        )
                            && $params['super_attribute'][$optionId] == $option['value_index']
                        ) {
                            unset($this->errors[self::PRODUCT_CONFIGURABLE_OPTION][$optionId]);
                        }
                    }
                }
                
                // loop through the configurable attributes on the product to get the option title
                // foreach ($product->getTypeInstance()->getConfigurableAttributes($product) as $attribute) {
                //    if(isset($aOptionsResult[$attribute->getId()]) == true) {
                //        $optionsResult[$attribute->getId()] = "Invalid option selected for " . $attribute->getLabel();
                //    }
                // }
            }
        }
        
        if ($this->hasOptionErrors() == true) {
            return $this->errors;
        } else {
            return false;
        }
    }
    
    public function getResult()
    {
        print_r(json_encode([
            'success' => $this->success,
            'errors' => $this->errors,
            'message' => $this->message,
            'profile' => $this->profile
        ]));
    }

    public function setProfile($profile)
    {
        $this->profile = $profile;
    }
    
    public function getProfile()
    {
        return $this->profile;
    }

    public function setSuccess($bool, $message = null)
    {
        if ($message != null) {
            $this->message = $message;
        }
        $this->success = (bool) $bool;
    }
    
    public function addSuccessMessage($message = null)
    {
        if ($message != null) {
            if ($this->message != null) {
                $this->message .= "\n" .$message;
            } else {
                $this->message .= $message;
            }
        }
    }

    public function getSuccess()
    {
        return $this->success;
    }
    
    public function getMessage()
    {
        return $this->message;
    }
    
    public function getErrors()
    {
        return $this->errors;
    }

    public function addFormKeyError()
    {
        $this->errors[self::ERROR_TYPE_FORM_KEY][] = [
            "message" => "Invalid form key."
        ];
    }
    
    public function addProductError($productId = 0, $message = null)
    {
        $this->success = false;
        $this->errors[self::ERROR_TYPE_PRODUCT][] = [
            "message" => $message,
            "product_id" => $productId
        ];
    }
    
    public function addExceptionError($e)
    {
        $this->success = false;
        $this->errors[self::ERROR_TYPE_EXCEPTION][] = [
            "message" => $e->getMessage()
        ];
    }
}
