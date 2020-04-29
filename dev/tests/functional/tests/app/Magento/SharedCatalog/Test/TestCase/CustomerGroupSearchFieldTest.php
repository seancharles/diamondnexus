<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SharedCatalog\Test\TestCase;

use Magento\Customer\Test\Fixture\CustomerGroup;
use Magento\Mtf\ObjectManager;
use Magento\Mtf\TestCase\Injectable;
use Magento\Mtf\Fixture\FixtureFactory;

/**
 * Preconditions:
 * 1. Create customer groups.
 *
 * Steps:
 * 1. Go to backend.
 * 2. Open "Product new" page.
 * 3. Go to "Advanced Pricing".
 * 4. Check customer groups field.
 * 5. Navigate to Marketing > Invitations.
 * 6. Open "Private Sales" page.
 * 7. Check customer groups field.
 *
 * @group SharedCatalog
 * @ZephyrId MAGETWO-68397
 */
class CustomerGroupSearchFieldTest extends Injectable
{
    /* tags */
    const MVP       = 'yes';
    const TEST_TYPE = 'acceptance_test';
    /* end tags */

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
        for ($i = 1; $i <= 20; $i++) {
            $customerGroup = $this->fixtureFactory->createByCode('customerGroup');
            $customerGroup->persist();
        }
        return ['customerGroupName' => $customerGroup->getCustomerGroupCode()];
    }
}
