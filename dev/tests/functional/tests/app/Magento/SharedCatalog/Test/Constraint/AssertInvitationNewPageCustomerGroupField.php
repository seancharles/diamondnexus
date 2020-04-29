<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SharedCatalog\Test\Constraint;

use Magento\Invitation\Test\Page\Adminhtml\InvitationsIndex;
use Magento\Invitation\Test\Page\Adminhtml\InvitationsIndexNew;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Check option in customer group field after typing text in search input on "Invitation new" page.
 */
class AssertInvitationNewPageCustomerGroupField extends AbstractConstraint
{
    /**
     * @param InvitationsIndex $invitationsIndex
     * @param InvitationsIndexNew $invitationsIndexNew
     * @param string $customerGroupName
     * @return void
     */
    public function processAssert(
        InvitationsIndex $invitationsIndex,
        InvitationsIndexNew $invitationsIndexNew,
        $customerGroupName
    ) {
        $invitationsIndex->open();
        $invitationsIndex->getGridPageActions()->addNew();
        $invitationsIndexNew->getCustomerGroup()->searchGroupByName($customerGroupName);
        \PHPUnit\Framework\Assert::assertEquals(
            $customerGroupName,
            $invitationsIndexNew->getCustomerGroup()->getResultFromField(),
            'Customer group field is not correct.'
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Customer group field is correct.';
    }
}
