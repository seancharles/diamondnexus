<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace ForeverCompanies\StonesIntermediary\Api;

use ForeverCompanies\StonesIntermediary\Api\Data\StonesSupplierInterface;
use Magento\Framework\Api\SearchCriteriaInterface;

interface StonesSupplierManagementInterface
{

    /**
     * GET for StonesIntermediary api
     * @param SearchCriteriaInterface $searchCriteria
     * @return \ForeverCompanies\StonesIntermediary\Api\Data\StonesSupplierSearchResultsInterface
     */
    public function getStonesSupplier(SearchCriteriaInterface $searchCriteria);

    /**
     * POST for StonesIntermediary api
     * @param StonesSupplierInterface $data
     * @return string
     */
    public function postStonesSupplier(StonesSupplierInterface $data);

    /**
     * @param StonesSupplierInterface $data
     * @return string
     */
    public function putStonesSupplier(StonesSupplierInterface $data);

    /**
     * @param int $id
     * @return string
     */
    public function deleteStonesSupplier(int $id);
}
