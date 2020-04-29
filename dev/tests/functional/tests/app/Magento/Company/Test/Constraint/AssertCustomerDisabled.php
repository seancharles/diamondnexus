<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Company\Test\Constraint;

use Magento\Customer\Test\Page\Adminhtml\CustomerIndex;
use Magento\Mtf\Client\BrowserInterface;
use Magento\Mtf\Client\Locator;
use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Mtf\Fixture\FixtureInterface;

/**
 * Assert customer is disabled.
 */
class AssertCustomerDisabled extends AbstractConstraint
{
    /**
     * Assert customer is disabled.
     *
     * @param CustomerIndex $customerIndex
     * @param FixtureInterface $entity
     * @param BrowserInterface $browser
     */
    public function processAssert(CustomerIndex $customerIndex, FixtureInterface $entity, BrowserInterface $browser)
    {
        $customer = $entity->getData();

        $nameParts = ['prefix', 'firstname', 'middlename', 'lastname', 'suffix'];
        $filteredNameParts = array_intersect_key($customer, array_flip($nameParts));
        $name = implode(' ', $filteredNameParts);
        $filter = [
            'name' => $name,
            'email' => $customer['email'],
        ];
        $customerIndex->open();
        $customerIndex->getCustomerGridBlock()->search($filter);
        $element = $browser->find('//*[text()[contains(.,\'Inactive\')]]', Locator::SELECTOR_XPATH);

        \PHPUnit\Framework\Assert::assertTrue(
            $element->isPresent(),
            'Customer is not disabled'
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Customer is disabled.';
    }
}
