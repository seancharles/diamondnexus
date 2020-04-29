<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Company\Test\Constraint;

use Magento\Mtf\Constraint\AbstractAssertForm;
use Magento\Company\Test\Page\Company as CompanyPage;
use Magento\Mtf\Fixture\FixtureInterface;

/**
 * Assert that data in form is equal to the original one.
 */
class AssertPopupFormOnStorefront extends AbstractAssertForm
{
    /**
     * Assert that data in form is equal to the original one.
     *
     * @param FixtureInterface $entity
     * @param string $popupMethod
     * @param CompanyPage $companyPage
     */
    public function processAssert(
        FixtureInterface $entity,
        $popupMethod,
        CompanyPage $companyPage
    ) {
        $companyPage->open();
        $companyPage->getTree()->selectFirstChild();
        $companyPage->getTreeControl()->clickEditSelected();
        $formData = $companyPage->$popupMethod()->getData($entity);
        $errors = $this->verifyData($entity->getData(), $formData);
        \PHPUnit\Framework\Assert::assertEmpty($errors, $errors);
    }

    /**
     * Data equals data from fixture.
     *
     * @return string
     */
    public function toString()
    {
        return 'Data equals data from fixture.';
    }
}
