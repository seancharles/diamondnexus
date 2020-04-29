<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Company\Test\Constraint;

/**
 * Assert that correct success message is displayed, after customer is created.
 */
class AssertCustomerCreatedMessage extends AbstractAssertCustomerMessage
{
    /**
     * Success message.
     *
     * @var string
     */
    protected $successMessage = 'The customer was successfully created.';
}
