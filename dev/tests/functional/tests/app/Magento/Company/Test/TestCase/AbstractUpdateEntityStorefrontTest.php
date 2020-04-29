<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Company\Test\TestCase;

use Magento\Customer\Test\Fixture\Customer;

/**
 * Abstract test update entity on Storefront.
 */
abstract class AbstractUpdateEntityStorefrontTest extends AbstractCreateEntityStorefrontTest
{
    /**
     * Update entity from Storefront.
     *
     * @param Customer $customer
     * @param string $entity
     * @param string $newEntity
     * @param string $configData
     * @return array
     */
    public function test(Customer $customer, $entity, $newEntity = null, $configData = null)
    {
        $result = parent::test($customer, $entity, $configData);
        $popupMethod = $result['popupMethod'];

        list($code, $dataset) = explode('/', $newEntity);
        $newEntity = $this->fixtureFactory->createByCode($code, ['dataset' => $dataset]);

        $this->companyPage->getTree()->selectFirstChild();
        $this->companyPage->getTreeControl()->clickEditSelected();
        $this->companyPage->$popupMethod()->fill($newEntity);
        $this->companyPage->$popupMethod()->submit();

        return ['entity' => $newEntity, 'popupMethod' => $popupMethod];
    }
}
