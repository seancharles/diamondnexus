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
     * @param bool $flagFishbowlUpdate
     * @return string
     */
    public function getExtSalesOrderUpdate(bool $flagFishbowlUpdate);

    /**
     * POST for ExtSalesOrderUpdate api
     * @param int $orderId
     * @param string $updatedFields
     * @param bool $flagFishbowlUpdate
     * @return string
     */
    public function postExtSalesOrderUpdate(int $orderId, string $updatedFields, bool $flagFishbowlUpdate);

    /**
     * @param int $orderId
     * @param bool $flagFishbowlUpdate
     * @return string
     */
    public function putExtSalesOrderUpdate(int $orderId, bool $flagFishbowlUpdate);
}
