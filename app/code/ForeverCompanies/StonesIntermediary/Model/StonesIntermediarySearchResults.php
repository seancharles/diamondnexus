<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace ForeverCompanies\StonesIntermediary\Model;

use ForeverCompanies\StonesIntermediary\Api\Data\StonesIntermediarySearchResultsInterface;
use Magento\Framework\Api\SearchResults;

/**
 * Service Data Object with Product search results.
 */
class StonesIntermediarySearchResults extends SearchResults implements StonesIntermediarySearchResultsInterface
{
}
