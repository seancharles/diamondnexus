<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace ForeverCompanies\CustomApi\Api;

interface ReindexInterface
{

    /**
     * @param int[] $productIds
     * @return string
     */
    public function reindexProducts(array $productIds);

    /**
     * @param int[] $categoryIds
     * @return string
     */
    public function reindexCategories(array $categoryIds);

}
