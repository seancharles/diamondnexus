<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SharedCatalog\Test\TestCase;

use Magento\Customer\Test\Fixture\CustomerGroup;
use Magento\SharedCatalog\Test\Fixture\SharedCatalog;
use Magento\Mtf\ObjectManager;
use Magento\Mtf\TestCase\Injectable;
use Magento\Mtf\Fixture\FixtureFactory;

/**
 * Preconditions:
 * 1. Create customer groups.
 *
 * Steps:
 * 1. Open "New Cart Price Rule" page.
 * 2. Check "Customer group" multi-select dropdown and enter the name of existing Customer group in the searches field.
 * 3. Open "New Cart Price Rule" page.
 * 4. Check "Customer group" multi-select dropdown and enter the name of existing Shared Catalog in the searches field.
 * 5. Perform all assertions.
 *
 * @group SharedCatalog
 * @ZephyrId MAGETWO-68427
 */
class MultiselectComponentForSearchFieldsTest extends Injectable
{
    /**
     * Fixture factory.
     *
     * @var FixtureFactory
     */
    private $fixtureFactory;

    /**
     * Injection data.
     *
     * @param FixtureFactory $fixtureFactory
     */
    public function __inject(
        FixtureFactory $fixtureFactory
    ) {
        $this->fixtureFactory = $fixtureFactory;
    }

    /**
     * Customer groups searches test.
     *
     * @param CustomerGroup $customerGroup
     * @return array
     */
    public function test(CustomerGroup $customerGroup)
    {
        // Preconditions:
        for ($i = 1; $i <= 3; $i++) {
            $customerGroup = $this->fixtureFactory->createByCode('customerGroup');
            $customerGroup->persist();
            $sharedCatalog = $this->fixtureFactory->createByCode(
                'shared_catalog',
                ['dataset' => 'shared_catalog_default']
            );
            $sharedCatalog->persist();
        }
        $catalogRule['data']['customer_group_ids'] = $customerGroup->getCustomerGroupCode();
        $catalogPriceRule = $this->fixtureFactory->createByCode('catalogRule', $catalogRule);
        $salesRule['data']['customer_group_ids'] = $sharedCatalog->getName();
        $cartPriceRule = $this->fixtureFactory->createByCode('salesRule', $salesRule);

        return [
            'customerGroup' => $customerGroup,
            'catalogPriceRule' => $catalogPriceRule,
            'sharedCatalog' => $sharedCatalog,
            'cartPriceRule' => $cartPriceRule
        ];
    }
}
