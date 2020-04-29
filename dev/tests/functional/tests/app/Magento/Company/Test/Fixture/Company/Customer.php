<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Company\Test\Fixture\Company;

use Magento\Mtf\Fixture\DataSource;
use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Customer\Test\Fixture\Customer as CustomerFixture;

/**
 * Prepare company admin.
 */
class Customer extends DataSource
{
    /**
     * Customer Fixture.
     *
     * @var CustomerFixture
     */
    private $customer;

    /**
     * Fixture Factory instance.
     *
     * @var FixtureFactory
     */
    private $fixtureFactory;

    /**
     * Fixture field data.
     *
     * @var array
     */
    private $fixtureData = null;

    /**
     * @constructor
     * @param FixtureFactory $fixtureFactory
     * @param array $data [optional]
     */
    public function __construct(
        FixtureFactory $fixtureFactory,
        $data = []
    ) {
        $this->fixtureFactory = $fixtureFactory;
        $this->fixtureData = $data;
    }

    /**
     * Return prepared data set.
     *
     * @param string|null $key [optional]
     * @return mixed
     */
    public function getData($key = null)
    {
        if (isset($this->fixtureData['dataset'])) {
            $customer = $this->fixtureFactory->createByCode('customer', ['dataset' => $this->fixtureData['dataset']]);
            if ($customer->hasData('id') === false) {
                $customer->persist();
            }
            $this->data[] = $customer->getEmail();
            $this->customer = $customer;
        }

        return parent::getData($key);
    }

    /**
     * Retrieve company admin.
     *
     * @return CustomerFixture
     */
    public function getCustomer()
    {
        return $this->customer;
    }
}
