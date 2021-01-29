<?php

    namespace ForeverCompanies\Salesforce\Helper;

    class Mapping
    {
        public function translateCustomerFields(&$aData = [], $aPost = []) {
            // converts all magento fields to SF equivalent
            foreach($aPost as $key => $value) {
                $aData[$this->getCustomerField($key)] = $value;
            }
        }
        
        public function translateOrderFields(&$aData = [], $aPost = []) {
            // converts all magento fields to SF equivalent
            foreach($aPost as $key => $value) {
                $aData[$this->getCustomerField($key)] = $value;
            }
        }
        
        public function getStoreCode($storeId = 0) {
            return \ForeverCompanies\Salesforce\Model\Constant::WEBSITE_MAPPING[$storeId];
        }
        
        public function getFormCode($formId = 0) {
            return \ForeverCompanies\Salesforce\Model\Constant::FORM_MAPPING[$formId];
        }
        
        public function getCustomerField($field = null) {
            return \ForeverCompanies\Salesforce\Model\Constant::WEBSITE_MAPPING[$field];
        }
        
        public function getOrderField($field = null) {
            return \ForeverCompanies\Salesforce\Model\Constant::WEBSITE_MAPPING[$field];
        }
    }