<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace ForeverCompanies\CustomApi\Api;

interface ExtSalesOrderUpdateManagementInterface
{

    /**
     * GET for ExtSalesOrderUpdate api
     * @param string $flagFishbowlUpdate
     * @return string
     */
    public function getExtSalesOrderUpdate($flagFishbowlUpdate);

    /**
     * POST for ExtSalesOrderUpdate api
     * @param int $orderId
     * @param string $updatedFields
     * @param int $flagFishbowlUpdate
     * @return string
     */
    public function postExtSalesOrderUpdate(int $orderId, string $updatedFields, int $flagFishbowlUpdate);
}
