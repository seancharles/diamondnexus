<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace ForeverCompanies\StonesIntermediary\Api;

use ForeverCompanies\StonesIntermediary\Api\Data\StonesIntermediaryInterface;
use ForeverCompanies\StonesIntermediary\Api\Data\StonesIntermediarySearchResultsInterface;
use Magento\Framework\Api\SearchCriteriaInterface;

interface StonesIntermediaryManagementInterface
{

    /**
     * GET for StonesIntermediary api
     * @param SearchCriteriaInterface $searchCriteria
     * @return StonesIntermediarySearchResultsInterface
     */
    public function getStonesIntermediary(SearchCriteriaInterface $searchCriteria);

    /**
     * POST for StonesIntermediary api
     * @param StonesIntermediaryInterface $data
     * @return string
     */
    public function postStonesIntermediary(StonesIntermediaryInterface $data);

    /**
     * @param StonesIntermediaryInterface $data
     * @return string
     */
    public function putStonesIntermediary(StonesIntermediaryInterface $data);

    /**
     * @param int $id
     * @return string
     */
    public function deleteStonesIntermediary(int $id);
}
