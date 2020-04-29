<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SharedCatalog\Test\TestCase;

use Magento\Mtf\TestCase\Injectable;
use Magento\SharedCatalog\Test\Fixture\SharedCatalog;

/**
 * Preconditions:
 * 1. Create 3 companies.
 * 2. Create 3 custom shared catalogs assigned to companies.
 * 3. Create public shared catalog.
 *
 * Steps:
 * 1. Open Shared catalog list.
 * 2. Find first shared catalog in list.
 * 3. Choose Companies from action list.
 * 4. Click filters section.
 * 5. Go to columns section.
 * 6. Remove default assigned filter.
 * 7. Sort the grid by 'assigned' field.
 * 8. Sort the grid by 'shared catalog' field.
 * 9. Go to filters section.
 * 10. Apply 'assigned' = 'No' filter.
 * 11. Make assertions.
 *
 * @group SharedCatalog
 * @ZephyrId MAGETWO-68637
 */
class CheckCompaniesColumnsAndFiltersTest extends Injectable
{
    /**
     * @var \Magento\SharedCatalog\Test\Page\Adminhtml\SharedCatalogIndex $sharedCatalogIndex
     */
    private $sharedCatalogIndex;

    /**
     * @var \Magento\SharedCatalog\Test\Page\Adminhtml\SharedCatalogCompany $sharedCatalogCompany
     */
    private $sharedCatalogCompany;

    /**
     * Test step factory.
     *
     * @var \Magento\Mtf\TestStep\TestStepFactory
     */
    private $stepFactory;

    /**
     * Configuration settings.
     *
     * @var string
     */
    private $configData;

    /**
     * @var \Magento\Mtf\Fixture\FixtureFactory
     */
    private $fixtureFactory;

    /**
     * Inject pages.
     *
     * @param \Magento\SharedCatalog\Test\Page\Adminhtml\SharedCatalogIndex $sharedCatalogIndex
     * @param \Magento\SharedCatalog\Test\Page\Adminhtml\SharedCatalogCompany $sharedCatalogCompany
     * @param \Magento\Mtf\TestStep\TestStepFactory $stepFactory
     * @param \Magento\Mtf\Fixture\FixtureFactory $fixtureFactory
     * @return void
     */
    public function __inject(
        \Magento\SharedCatalog\Test\Page\Adminhtml\SharedCatalogIndex $sharedCatalogIndex,
        \Magento\SharedCatalog\Test\Page\Adminhtml\SharedCatalogCompany $sharedCatalogCompany,
        \Magento\Mtf\TestStep\TestStepFactory $stepFactory,
        \Magento\Mtf\Fixture\FixtureFactory $fixtureFactory
    ) {
        $this->sharedCatalogIndex = $sharedCatalogIndex;
        $this->sharedCatalogCompany = $sharedCatalogCompany;
        $this->stepFactory = $stepFactory;
        $this->fixtureFactory = $fixtureFactory;
    }

    /**
     * Test for shared catalog companies grid (filters, columns, sorting).
     *
     * @param array $sharedCatalogDataSets
     * @param string|null $configData [optional]
     * @return array
     */
    public function test(
        array $sharedCatalogDataSets,
        $configData = null
    ) {
        // Preconditions
        $this->configData = $configData;
        $this->stepFactory->create(
            \Magento\Config\Test\TestStep\SetupConfigurationStep::class,
            ['configData' => $this->configData]
        )->run();
        $sharedCatalogs = [];
        foreach ($sharedCatalogDataSets as $sharedCatalogDataSet) {
            $sharedCatalog = $this->fixtureFactory->createByCode(
                'shared_catalog',
                [
                    'dataset' => $sharedCatalogDataSet,
                ]
            );
            $sharedCatalog->persist();
            $sharedCatalogs[] = $sharedCatalog;
        }

        // Test steps.
        $this->sharedCatalogIndex->open();
        $sharedCatalogToAssign = array_shift($sharedCatalogs);
        $this->sharedCatalogIndex->getGrid()->search(['name' => $sharedCatalogToAssign->getName()]);
        $sharedCatalogId = $this->sharedCatalogIndex->getGrid()->getFirstItemId();
        $this->sharedCatalogIndex->getGrid()->openCompanies($sharedCatalogId);

        $assignedCompanyName = $sharedCatalogToAssign->getDataFieldConfig('companies')['source']->getCompanies()[0]
            ->getCompanyName();

        return [
            'sharedCatalogCompany' => $this->sharedCatalogCompany,
            'expectedAssignedCompanyName' => $assignedCompanyName
        ];
    }

    /**
     * Roll back configuration settings.
     *
     * @return void
     */
    protected function tearDown()
    {
        $this->stepFactory->create(
            \Magento\Config\Test\TestStep\SetupConfigurationStep::class,
            ['configData' => $this->configData, 'rollback' => true]
        )->run();
    }
}
