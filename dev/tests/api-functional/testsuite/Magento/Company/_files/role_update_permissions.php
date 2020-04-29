<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

return [
    [
        'requestPermissions' => [
            ['resource_id' => 'Magento_Company::index', 'permission' => 'allow'],
            ['resource_id' => 'Magento_NegotiableQuote::all', 'permission' => 'allow'],
            ['resource_id' => 'Magento_NegotiableQuote::view_quotes', 'permission' => 'allow'],
            ['resource_id' => 'Magento_NegotiableQuote::manage', 'permission' => 'allow'],
            ['resource_id' => 'Magento_NegotiableQuote::checkout', 'permission' => 'allow'],
            ['resource_id' => 'Magento_NegotiableQuote::view_quotes_sub', 'permission' => 'allow'],
            ['resource_id' => 'Magento_Company::credit', 'permission' => 'allow'],
            ['resource_id' => 'Magento_Company::credit_history', 'permission' => 'allow'],
        ],
        'responsePermissions' => [
            ['resource_id' => 'Magento_Company::index', 'permission' => 'allow'],
            ['resource_id' => 'Magento_Sales::all', 'permission' => 'deny'],
            ['resource_id' => 'Magento_Sales::place_order', 'permission' => 'deny'],
            ['resource_id' => 'Magento_Sales::payment_account', 'permission' => 'deny'],
            ['resource_id' => 'Magento_Sales::view_orders', 'permission' => 'deny'],
            ['resource_id' => 'Magento_Sales::view_orders_sub', 'permission' => 'deny'],
            ['resource_id' => 'Magento_NegotiableQuote::all', 'permission' => 'allow'],
            ['resource_id' => 'Magento_NegotiableQuote::view_quotes', 'permission' => 'allow'],
            ['resource_id' => 'Magento_NegotiableQuote::manage', 'permission' => 'allow'],
            ['resource_id' => 'Magento_NegotiableQuote::checkout', 'permission' => 'allow'],
            ['resource_id' => 'Magento_NegotiableQuote::view_quotes_sub', 'permission' => 'allow'],
            ['resource_id' => 'Magento_Company::view', 'permission' => 'deny'],
            ['resource_id' => 'Magento_Company::view_account', 'permission' => 'deny'],
            ['resource_id' => 'Magento_Company::edit_account', 'permission' => 'deny'],
            ['resource_id' => 'Magento_Company::view_address', 'permission' => 'deny'],
            ['resource_id' => 'Magento_Company::edit_address', 'permission' => 'deny'],
            ['resource_id' => 'Magento_Company::contacts', 'permission' => 'deny'],
            ['resource_id' => 'Magento_Company::payment_information', 'permission' => 'deny'],
            ['resource_id' => 'Magento_Company::user_management', 'permission' => 'deny'],
            ['resource_id' => 'Magento_Company::roles_view', 'permission' => 'deny'],
            ['resource_id' => 'Magento_Company::roles_edit', 'permission' => 'deny'],
            ['resource_id' => 'Magento_Company::users_view', 'permission' => 'deny'],
            ['resource_id' => 'Magento_Company::users_edit', 'permission' => 'deny'],
            ['resource_id' => 'Magento_Company::credit', 'permission' => 'allow'],
            ['resource_id' => 'Magento_Company::credit_history', 'permission' => 'allow'],
        ],
    ],
];
