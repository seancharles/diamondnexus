<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace ForeverCompanies\CustomApi\Api;

use Magento\Framework\Api\SearchCriteriaInterface;

interface ExtSalesOrderUpdateManagementInterface
{

    /**
     * GET for ExtSalesOrderUpdate api
     * @param SearchCriteriaInterface $searchCriteria
     * @return \ForeverCompanies\CustomApi\Api\Data\ExtSearchResultsInterface
     */
    public function getExtSalesOrderUpdate(SearchCriteriaInterface $searchCriteria);

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
