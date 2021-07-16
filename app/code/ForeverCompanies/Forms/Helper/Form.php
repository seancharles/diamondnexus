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
        return filter_var($this->request->getParam($fieldName), FILTER_SANITIZE_SPECIAL_CHARS);
    }
    
    public function sanitize($value)
    {
        return $value;
    }
}
