<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace ForeverCompanies\Salesforce\Api;

interface OrderSalesforceInterface
{
    /**
     * Save order via REST API endpoint in Salesforce
     *
     * @api
     * @return \Magento\Sales\Api\Data\OrderInterface[]
     */
    public function createOrder();

}
