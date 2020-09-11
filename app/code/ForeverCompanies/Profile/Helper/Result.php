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
	
	protected $errors = array();
	protected $success = false;
	
	// this is a singular response message
	protected $message = '';
	protected $profile = '';
	
	public function __construct(
		\Magento\Backend\App\Action\Context $context
	) {

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