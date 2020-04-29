<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Company\Test\Constraint;

use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Company\Test\Page\CompanyUsers;

/**
 * Abstract assert that correct success message is displayed.
 */
abstract class AbstractAssertCustomerMessage extends AbstractConstraint
{
    /**
     * Success message.
     *
     * @var string
     */
    protected $successMessage = '';

    /**
     * Assert that correct success message is displayed.
     *
     * @param CompanyUsers $companyUsers
     */
    public function processAssert(CompanyUsers $companyUsers)
    {
        \PHPUnit\Framework\Assert::assertEquals(
            $this->successMessage,
            $companyUsers->getMessages()->getSuccessMessage(),
            'Success message is not correct.'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function toString()
    {
        return 'Correct success message is displayed.';
    }
}
