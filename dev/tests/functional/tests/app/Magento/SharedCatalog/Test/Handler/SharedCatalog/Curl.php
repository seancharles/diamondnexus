<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SharedCatalog\Test\Handler\SharedCatalog;

use Magento\Mtf\Fixture\FixtureInterface;
use Magento\Mtf\Handler\Curl as AbstractCurl;
use Magento\Mtf\Util\Protocol\CurlTransport;
use Magento\Mtf\Util\Protocol\CurlTransport\BackendDecorator;

/**
 * Class Curl
 * Curl handler for creating SharedCatalog
 */
class Curl extends AbstractCurl implements SharedCatalogInterface
{
    /**
     * Url for saving data
     *
     * @var string
     */
    protected $saveUrl = 'shared_catalog/sharedCatalog/save';

    /**
     * Mapping values for data
     *
     * @var array
     */
    protected $mappingData = [
        'name' => 'Shared Catalog',
        'description' => '',
        'type' => 0,
    ];

    /**
     * POST request for creating gift registry type
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
        $curl->write($url, ['catalog_details' => $data]);
        $response = $curl->read();
        $curl->close();
        if (strpos($response, 'Something went wrong while saving the shared catalog.') !== false) {
            throw new \Exception("Shared catalog creating by curl handler was not successful! Response: $response");
        }

        return ['shared_catalog' => $fixture];
    }

    /**
     * Prepare data for CURL request
     *
     * @param FixtureInterface $fixture
     * @return array
     */
    protected function prepareData($fixture)
    {
        $data = $this->replaceMappingData($fixture->getData());
        $data['tax_class_id'] = $fixture->getDataFieldConfig('tax_class_id')['source']
            ->getFixture()[0]
            ->getData()['id'];
        return $data;
    }
}
