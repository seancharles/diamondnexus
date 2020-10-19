<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace ForeverCompanies\Salesforce\Api;

interface CustomerSalesforceInterface
{
    /**
     * Save customer via REST API endpoint in Salesforce
     *
     * @api
     * @return \Magento\Customer\Api\Data\CustomerInterface
     */
    public function createCustomer();

}
