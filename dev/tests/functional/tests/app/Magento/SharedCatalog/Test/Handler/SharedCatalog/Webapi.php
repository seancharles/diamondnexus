<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SharedCatalog\Test\Handler\SharedCatalog;

use Magento\Mtf\Fixture\FixtureInterface;
use Magento\Mtf\Handler\Webapi as AbstractWebapi;

/**
 * Create new Shared Catalog via webapi.
 */
class Webapi extends AbstractWebapi implements SharedCatalogInterface
{
    /**
     * WebAPI request for creating new Shared Catalog.
     *
     * @param FixtureInterface|null $fixture [optional]
     * @return array
     * @throws \Exception
     */
    public function persist(FixtureInterface $fixture = null)
    {
        $data = $this->prepareData($fixture);
        $url = $_ENV['app_frontend_url'] . 'rest/V1/sharedCatalog';

        $this->webapiTransport->write($url, $data);
        $response = json_decode($this->webapiTransport->read(), true);
        $this->webapiTransport->close();

        if (!((int)$response > 0)) {
            $this->eventManager->dispatchEvent(['webapi_failed'], [$response]);
            throw new \Exception('Shared catalog creation by webapi handler was not successful!');
        }
        $this->assignCompanies($fixture, $response);

        return ['id' => $response];
    }

    /**
     * Assign companies to shared catalog.
     *
     * @param FixtureInterface $fixture
     * @param int $sharedCatalogId
     * @return void
     * @throws \Exception
     */
    private function assignCompanies(FixtureInterface $fixture, $sharedCatalogId)
    {
        if (isset($fixture->getData()['companies'])) {
            $url = $_ENV['app_frontend_url'] . 'rest/V1/sharedCatalog/' . $sharedCatalogId . '/assignCompanies';
            $companies = $fixture->getDataFieldConfig('companies')['source']->getCompanies();
            $this->webapiTransport->write($url, $this->prepareCompaniesData($companies));
            $response = json_decode($this->webapiTransport->read(), true);
            $this->webapiTransport->close();

            if ($response !== true) {
                $this->eventManager->dispatchEvent(['webapi_failed'], [$response]);
                throw new \Exception('Company assignment to shared catalog by webapi handler was not successful!');
            }
        }
    }

    /**
     * Prepare companies data for WebAPI request.
     *
     * @param array $companies
     * @return array
     */
    private function prepareCompaniesData(array $companies)
    {
        $companiesData = [];
        foreach ($companies as $company) {
            $rowData = $company->getData();
            $rowData['street'] = (array)$rowData['street'];
            unset(
                $rowData['firstname'],
                $rowData['lastname'],
                $rowData['customer'],
                $rowData['email'],
                $rowData['website_id']
            );
            $companyAdmin = $company->getDataFieldConfig('customer')['source']->getCustomer();
            $rowData['customer_group_id'] = $companyAdmin->getDataFieldConfig('group_id')['source']
                ->getCustomerGroup()->getCustomerGroupId();
            $rowData['super_user_id'] = $companyAdmin->getId();
            $companiesData[] = $rowData;
        }

        return ['companies' => $companiesData];
    }

    /**
     * Prepare shared catalog data for WebAPI request.
     *
     * @param FixtureInterface $fixture
     * @return array
     */
    protected function prepareData(FixtureInterface $fixture)
    {
        $data = $this->replaceMappingData($fixture->getData());
        $data['tax_class_id'] = $fixture->getDataFieldConfig('tax_class_id')['source']
            ->getFixture()[0]
            ->getData()['id'];
        $data['store_id'] = 0;
        unset($data['companies']);

        return ['sharedCatalog' => $data];
    }
}
