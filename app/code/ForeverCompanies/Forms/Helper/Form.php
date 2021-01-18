<?php

namespace ForeverCompanies\Forms\Helper;
 
class Form
{
	public function __construct(
        \Magento\Framework\App\RequestInterface $request
    ) {
		$this->request = $request;
	}
	
	public function getSanitizedField($fieldName = null)
	{
		return $this->request->getParam($fieldName);
	}
	
	public function sanitize($value) {
		return $value;
	}
}