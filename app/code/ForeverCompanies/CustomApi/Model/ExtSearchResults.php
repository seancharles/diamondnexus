<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace ForeverCompanies\CustomApi\Model;

use ForeverCompanies\CustomApi\Api\Data\ExtSearchResultsInterface;
use Magento\Framework\Api\SearchResults;

/**
 * Service Data Object with Product search results.
 */
class ExtSearchResults extends SearchResults implements ExtSearchResultsInterface
{
}
