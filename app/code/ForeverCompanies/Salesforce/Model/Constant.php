<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace ForeverCompanies\Salesforce\Model;

class Constant
{
    const FORM_MAPPING = [
        8 => 'fa-short',
        9 => 'fa-long',
        10 => 'fa-long',
        14 => 'tf-short'
    ];

    const WEBSITE_MAPPING = [
        1 => 'DN',
        2 => 'FA',
        3 => '1215'
    ];

    const LEAD_FIELD_MAPPING = [
        'firstname' => 'FirstName',
        'lastname' => 'LastName',
        'phone' => 'Phone'
    ];
    
    const ACCOUNT_FIELD_MAPPING = [
        'entity_id' => 'Web_Account_Id__c',
        'firstname' => 'FirstName',
        'lastname' => 'LastName',
        'email' => 'PersonEmail',
        'bill_city' => 'BillingCity',
        'bill_region' => 'BillingState',
        'bill_country_id' => 'BillingCountry',
        'bill_postcode' => 'BillingPostalCode',
        'bill_street' => 'BillingStreet',
        'bill_telephone' => 'Phone',
        'group_id' => 'Customer_Group__c',
        'military' => 'Military_Discount__c',
        'rewards_points' => 'Reward_Points_Available__c',
        'rewards_pendingpoints' => 'Reward_Points_Pending__c',
        'credit' => 'Store_Credit_Available__c',
        'dob' => 'PersonBirthdate',
        'anniversary' => 'Anniversary__c',
        'dob2' => 'Birthday_of_Significant_Other__c',
        'mobilephone' => 'PersonMobilePhone',
        'ship_street' => 'ShippingStreet',
        'ship_city' => 'ShippingCity',
        'ship_region' => 'ShippingState',
        'ship_country_id' => 'ShippingCountry',
        'ship_postcode' => 'ShippingPostalCode',
        'gender' => 'Gender__c',
        'bill_fax' => 'Fax'
    ];
    
    const ORDER_FIELD_MAPPING = [
        'entity_id' => 'Web_Order_Id__c',
        'bill_street' => 'BillingStreet',
        'bill_city' => 'BillingCity',
        'bill_region' => 'BillingState',
        'bill_postcode' => 'BillingPostalCode',
        'bill_country_id' => 'BillingCountry',
        'ship_street' => 'ShippingStreet',
        'ship_city' => 'ShippingCity',
        'ship_region' => 'ShippingState',
        'ship_postcode' => 'ShippingPostalCode',
        'ship_country_id' => 'ShippingCountry',
        'shipping_date' => 'Ship_Date__c',
        'subtotal' => 'Order_Subtotal__c',
        'order_summary' => 'Order_Summary__c',
        'order_description' => 'Description',
        'discount_amount' => 'Discount_Amount__c',
        'shipping_tracknum' => 'Ship_Tracking_Number__c',
        'grand_total' => 'Order_Total__c',
        'status' => 'Order_Status__c',
        'shipping_description' => 'Ship_Method__c',
        'rewards_earnedpoints' => 'Reward_Points_Earned__c',
        'rewards_spentpoints' => 'Reward_Points_Spent__c',
        'store_name' => 'Store_Name__c',
        'order_notes' => 'Order_Comments__c',
        'increment_id' => 'Web_Order_Number__c',
        'tax_amount' => 'Tax_Amount__c',
        'sf_status' => 'Status'
    ];
}