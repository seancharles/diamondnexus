<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\TestFramework\Helper\Bootstrap;

require 'companies.php';

/** @var \Magento\SharedCatalog\Model\ResourceModel\SharedCatalog\Collection $sharedCatalogCollection */
$sharedCatalogCollection = Bootstrap::getObjectManager()->create(
    \Magento\SharedCatalog\Model\ResourceModel\SharedCatalog\Collection::class
);
$sharedCatalog = $sharedCatalogCollection->getLastItem();
/** @var \Magento\SharedCatalog\Api\SharedCatalogManagementInterface $sharedCatalogManagement */
$sharedCatalogManagement = Bootstrap::getObjectManager()->create(
    \Magento\SharedCatalog\Api\SharedCatalogManagementInterface::class
);
$publicCatalog = $sharedCatalogManagement->getPublicCatalog();
/** @var \Magento\SharedCatalog\Api\CompanyManagementInterface $companyManagement */
$companyManagement = Bootstrap::getObjectManager()->create(
    \Magento\SharedCatalog\Api\CompanyManagementInterface::class
);
$companyManagement->assignCompanies($sharedCatalog->getId(), $companies);
$companyManagement->assignCompanies($publicCatalog->getId(), $companies);
