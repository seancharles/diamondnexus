<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Company\Test\Handler\Company;

use Magento\Mtf\Util\Protocol\CurlInterface;
use Magento\Mtf\Fixture\FixtureInterface;
use Magento\Company\Test\Fixture\CompanyAttributes;
use Magento\Mtf\Handler\Webapi as AbstractWebapi;

/**
 * Web API based handler for creating customer company attributes.
 */
class AttributesWebapi extends AbstractWebapi implements CompanyAttributesInterface
{
    /**
     * Create company attributes via Web API.
     *
     * @param CompanyAttributes|FixtureInterface|null $attributes
     * @return array
     * @throws \Exception
     */
    public function persist(FixtureInterface $attributes = null)
    {
        $url = $_ENV['app_frontend_url'] . 'rest/V1/customers/' .$attributes->getCustomerId();
        //Getting customer data.
        $this->webapiTransport->write($url, [], CurlInterface::GET);
        $customerData = json_decode($this->webapiTransport->read(), true);
        $customerData['extension_attributes']['company_attributes'] = [
            'company_id' => $attributes->getCompanyId(),
            'job_title' => $attributes->getJobTitle(),
            'telephone' => $attributes->getTelephone(),
            'status' => $attributes->getStatus(),
            'customer_id' => $attributes->getCustomerId()
        ];

        $this->webapiTransport->write($url, ['customer' => $customerData], CurlInterface::PUT);
        $response = json_decode($this->webapiTransport->read(), true);
        $this->webapiTransport->close();

        if (empty($response['extension_attributes'])
            || empty($response['extension_attributes']['company_attributes'])
            || empty($response['extension_attributes']['company_attributes']['company_id'])
        ) {
            $this->eventManager->dispatchEvent(['webapi_failed'], [$response]);
            throw new \Exception('Company attributes creation by Web API handler was not successful!');
        }

        return ['company_id' => $response['extension_attributes']['company_attributes']['company_id']];
    }
}
