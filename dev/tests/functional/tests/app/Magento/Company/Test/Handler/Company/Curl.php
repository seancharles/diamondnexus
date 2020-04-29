<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Company\Test\Handler\Company;

use Magento\Mtf\Util\Protocol\CurlInterface;
use Magento\Mtf\Fixture\FixtureInterface;
use Magento\Mtf\Handler\Curl as AbstractCurl;
use Magento\Mtf\Util\Protocol\CurlTransport;
use Magento\Mtf\Util\Protocol\CurlTransport\BackendDecorator;

/**
 * Curl handler for creating Company.
 */
class Curl extends AbstractCurl implements CompanyInterface
{
    /**
     * Url for saving data.
     *
     * @var string
     */
    protected $saveUrl = 'company/index/save/';

    /**
     * Mapping values for data.
     *
     * @var array
     */
    protected $mappingData = [
        'status' => [
            'Pending Approval' => 0,
            'Active' => 1,
            'Rejected' => 2,
        ],
        'country_id' => [
            'Albania' => 'AL',
            'Andorra' => 'AD',
        ],
        'customer_group_id' => [
            'General' => 1,
        ],
        'website_id' => [
            'Main Website' => 1,
        ],
    ];

    /**
     * POST request for creating gift registry type.
     *
     * @param FixtureInterface|null $fixture [optional]
     * @throws \Exception
     * @return array
     */
    public function persist(FixtureInterface $fixture = null)
    {
        $data = $this->prepareData($fixture);
        $url = $_ENV['app_backend_url'] . $this->saveUrl;
        $curl = new BackendDecorator(new CurlTransport(), $this->_configuration);
        $curl->addOption(CURLOPT_HEADER, 1);
        $curl->write($url, $data);
        $response = $curl->read();
        $curl->close();

        if (strpos($response, 'data-ui-id="messages-message-success"') === false) {
            throw new \Exception("Company creating by curl handler was not successful! Response: $response");
        }

        return ['id' => $this->getCompanyId($data['company_admin']['email'])];
    }

    /**
     * Get company id by admin email.
     *
     * @param string $email
     * @return int|null
     */
    protected function getCompanyId($email)
    {
        $url = $_ENV['app_backend_url'] . 'mui/index/render/';
        $data = [
            'namespace' => 'company_listing',
            'filters' => [
                'placeholder' => true,
                'email_admin' => $email
            ],
            'isAjax' => true
        ];
        $curl = new BackendDecorator(new CurlTransport(), $this->_configuration);

        $curl->write($url, $data, CurlInterface::POST);
        $response = $curl->read();
        $curl->close();

        preg_match('/company_listing_data_source.+items.+"entity_id":"(\d+)"/', $response, $match);
        return empty($match[1]) ? null : (int)$match[1];
    }

    /**
     * Prepare data for CURL request.
     *
     * @param FixtureInterface $fixture
     * @return array
     */
    protected function prepareData($fixture)
    {
        $data = $this->replaceMappingData($fixture->getData());
        if (isset($fixture->getData()['customer'])) {
            $data['email'] = $fixture->getDataFieldConfig('customer')['source']->getCustomer()->getEmail();
        }
        $data['street'] = (array)$data['street'];
        unset($data['customer']);

        $groupedData = [];
        foreach ($data as $key => $value) {
            $fieldConfig = $fixture->getDataFieldConfig($key);
            if (!empty($fieldConfig['group'])) {
                $groupedData[$fieldConfig['group']][$key] = $value;
            } else {
                $groupedData[$key] = $value;
            }
        }

        return $groupedData;
    }
}
