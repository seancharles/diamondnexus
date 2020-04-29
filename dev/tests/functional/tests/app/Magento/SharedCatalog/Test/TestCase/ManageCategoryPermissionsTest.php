<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SharedCatalog\Test\TestCase;

use Magento\SharedCatalog\Test\Page\Adminhtml\SharedCatalogIndex;
use Magento\SharedCatalog\Test\Page\Adminhtml\SystemConfigBtob;
use Magento\Mtf\TestCase\Injectable;

/**
 * Steps:
 * 1. Login as admin.
 * 2. Open Shared Catalog page.
 * 3. Go to B2B system configuration page by clicking shared catalog link in admin notification.
 * 4. Enable Shared Catalog feature in system configuration B2B features.
 * 5. Perform assertions.
 *
 * @group SharedCatalog
 * @ZephyrId MAGETWO-68650
 */
class ManageCategoryPermissionsTest extends Injectable
{
    /**
     * @var \Magento\SharedCatalog\Test\Page\Adminhtml\SharedCatalogIndex $sharedCatalogIndex
     */
    private $sharedCatalogIndex;

    /**
     * @var \Magento\SharedCatalog\Test\Page\Adminhtml\SystemConfigBtob
     */
    private $systemConfigBtob;

    /**
     * @var string
     */
    private $configData;

    /**
     * Inject pages.
     *
     * @param SharedCatalogIndex $sharedCatalogIndex
     * @param SystemConfigBtob $systemConfigBtob
     * @return void
     */
    public function __inject(
        SharedCatalogIndex $sharedCatalogIndex,
        SystemConfigBtob $systemConfigBtob
    ) {
        $this->sharedCatalogIndex = $sharedCatalogIndex;
        $this->systemConfigBtob = $systemConfigBtob;
    }

    /**
     * Manage category permissions.
     *
     * @param string|null $sharedCatalogFeatureDisabledMessage [optional]
     * @param string|null $sectionDescription [optional]
     * @param string|null $sharedCatalogItemComment [optional]
     * @param array $steps [optional]
     * @param string|null $configData [optional]
     * @return array
     */
    public function test(
        $sharedCatalogFeatureDisabledMessage = null,
        $sectionDescription = null,
        $sharedCatalogItemComment = null,
        array $steps = [],
        $configData = null
    ) {
        $this->configData = $configData;
        $this->objectManager->create(
            'Magento\Config\Test\TestStep\SetupConfigurationStep',
            ['configData' => $this->configData]
        )->run();

        foreach ($steps as $methodName) {
            $this->$methodName();
        }

        return [
            'sharedCatalogFeatureDisabledMessage' => $sharedCatalogFeatureDisabledMessage,
            'sectionDescription' => $sectionDescription,
            'sharedCatalogItemComment' => $sharedCatalogItemComment
        ];
    }

    /**
     * Open B2B features system configuration page.
     *
     * @return void
     */
    private function openConfigurationFeaturesPage()
    {
        $this->sharedCatalogIndex->open();
        $this->sharedCatalogIndex->getSharedCatalogMessage()->openConfigurationFeaturesPage();
    }

    /**
     * Enable shared catalog B2B feature.
     *
     * @return void
     */
    private function enableSharedCatalogFeature()
    {
        $this->systemConfigBtob->open();
        $this->systemConfigBtob->getBtobFeatures()->enableSharedCatalogFeature();
        $this->systemConfigBtob->getFormPageActions()->save();
    }
}
