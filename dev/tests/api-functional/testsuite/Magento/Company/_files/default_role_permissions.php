<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

return [
    [
        'permissions' => [
            ['resource_id' => 'Magento_Company::index','permission' => 'allow'],
            ['resource_id' => 'Magento_Sales::all','permission' => 'allow'],
            ['resource_id' => 'Magento_Sales::place_order','permission' => 'allow'],
            ['resource_id' => 'Magento_Sales::payment_account','permission' => 'deny'],
            ['resource_id' => 'Magento_Sales::view_orders','permission' => 'allow'],
            ['resource_id' => 'Magento_Sales::view_orders_sub','permission' => 'deny'],
            ['resource_id' => 'Magento_NegotiableQuote::all','permission' => 'allow'],
            ['resource_id' => 'Magento_NegotiableQuote::view_quotes','permission' => 'allow'],
            ['resource_id' => 'Magento_NegotiableQuote::manage','permission' => 'allow'],
            ['resource_id' => 'Magento_NegotiableQuote::checkout','permission' => 'allow'],
            ['resource_id' => 'Magento_NegotiableQuote::view_quotes_sub','permission' => 'deny'],
            ['resource_id' => 'Magento_Company::view','permission' => 'allow'],
            ['resource_id' => 'Magento_Company::view_account','permission' => 'allow'],
            ['resource_id' => 'Magento_Company::edit_account','permission' => 'deny'],
            ['resource_id' => 'Magento_Company::view_address','permission' => 'allow'],
            ['resource_id' => 'Magento_Company::edit_address','permission' => 'deny'],
            ['resource_id' => 'Magento_Company::contacts','permission' => 'allow'],
            ['resource_id' => 'Magento_Company::payment_information','permission' => 'allow'],
            ['resource_id' => 'Magento_Company::user_management','permission' => 'allow'],
            ['resource_id' => 'Magento_Company::roles_view','permission' => 'deny'],
            ['resource_id' => 'Magento_Company::roles_edit','permission' => 'deny'],
            ['resource_id' => 'Magento_Company::users_view','permission' => 'allow'],
            ['resource_id' => 'Magento_Company::users_edit','permission' => 'deny'],
            ['resource_id' => 'Magento_Company::credit','permission' => 'deny'],
            ['resource_id' => 'Magento_Company::credit_history','permission' => 'deny'],
        ],
    ],
];
