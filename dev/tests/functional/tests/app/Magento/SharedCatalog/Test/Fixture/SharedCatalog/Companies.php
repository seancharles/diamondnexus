<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SharedCatalog\Test\Fixture\SharedCatalog;

use Magento\Mtf\Fixture\DataSource;
use Magento\Mtf\Fixture\FixtureFactory;

/**
 * Prepare shared catalog companies.
 */
class Companies extends DataSource
{
    /**
     * Companies Fixtures.
     *
     * @var array
     */
    private $companies;

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
     * @param FixtureFactory $fixtureFactory
     * @param array $params
     * @param array $data [optional]
     */
    public function __construct(
        FixtureFactory $fixtureFactory,
        array $params,
        $data = []
    ) {
        $this->fixtureFactory = $fixtureFactory;
        $this->params = $params;
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
        foreach ($this->fixtureData as $dataset) {
            if (isset($dataset['dataset'])) {
                $company = $this->fixtureFactory->createByCode('company', $dataset);

                if (!$company->hasData('id')) {
                    $company->persist();
                }
                $this->data[] = $company->getCompanyName();
                $this->companies[] = $company;
            }
        }

        return parent::getData($key);
    }

    /**
     * Retrieve companies assigned to shared catalog.
     *
     * @return array
     */
    public function getCompanies()
    {
        return $this->companies;
    }
}
