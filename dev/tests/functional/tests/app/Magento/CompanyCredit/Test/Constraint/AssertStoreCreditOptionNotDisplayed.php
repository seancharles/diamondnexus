<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CompanyCredit\Test\Constraint;

use Magento\Rma\Test\Page\Adminhtml\RmaIndex;
use Magento\Rma\Test\Page\Adminhtml\RmaNew;
use Magento\Rma\Test\Page\Adminhtml\RmaChooseOrder;
use Magento\Rma\Test\Fixture\Rma;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Assert Store Credit is not displayed on the Return form if order placed with Payment on Account method.
 */
class AssertStoreCreditOptionNotDisplayed extends AbstractConstraint
{
    const OPTION = 'Store Credit';

    /**
     * Assert Store Credit is not displayed on the Return form.
     *
     * @param RmaIndex $rmaIndex
     * @param RmaNew $rmaNew
     * @param RmaChooseOrder $rmaChooseOrder
     * @param string $orderId
     * @param Rma $rma
     * @return void
     */
    public function processAssert(
        RmaIndex $rmaIndex,
        RmaNew $rmaNew,
        RmaChooseOrder $rmaChooseOrder,
        Rma $rma,
        $orderId
    ) {
        $rmaIndex->open();
        $rmaIndex->getGridPageActions()->addNew();
        $rmaChooseOrder->getOrderGrid()->searchAndOpen(['id' => $orderId]);
        $rmaNew->getRmaForm()->fill($rma);
        $rmaNew->getRmaForm()->openTab('items');
        foreach ($rmaNew->getRmaItems()->getResolutionOptions() as $option) {
            \PHPUnit\Framework\Assert::assertNotEquals(
                self::OPTION,
                $option->getText(),
                sprintf('Option \'%s\' is available in Resolution field.', self::OPTION)
            );
        }
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Store Credit is not displayed on the Return form.';
    }
}
