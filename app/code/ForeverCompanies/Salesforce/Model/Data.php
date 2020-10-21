<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace ForeverCompanies\Salesforce\Model;

use Magento\Directory\Model\Country;

/**
 * Class Data
 *
 * @package ForeverCompanies\Salesforce\Model
 *
 */
class Data
{
    /**
     * @var \ForeverCompanies\Salesforce\Model\MapFactory
     */
    protected $mapFactory;

    /**
     * @var \ForeverCompanies\Salesforce\Model\Field
     */
    protected $field;

    /**
     * @var \Magento\Directory\Model\Country
     */
    protected $country;

    /**
     * Data constructor.
     *
     * @param MapFactory $map
     * @param Field $field
     * @param Country $country
     */
    public function __construct(
        MapFactory $map,
        Field $field,
        Country $country
    ) {
        $this->mapFactory = $map;
        $this->field      = $field;
        $this->country    = $country;
    }


    /**
     * Get Country Name
     *
     * @param string $id
     * @param string
     */
    public function getCountryName($id)
    {
        $model = $this->country->loadByCode($id);
        return $model->getName();
    }

    /**
     * Get all data of Customer
     *
     * @param  \Magento\Customer\Model\Customer $model
     * @param  string                           $type
     * @return array
     */
    public function getCustomer($model, $type)
    {
        $this->field->setType($type);
        $magento_fields = $this->field->getMagentoFields();
        $data = [];
        foreach ($magento_fields as $key => $item) {
            $sub = substr($key, 0, 5);
            if ($sub == 'bill_' && $model->getDefaultBillingAddress()) {
                $value      = substr($key, 5);
                $billing    = $model->getDefaultBillingAddress();
                $data[$key] = $billing->getData($value);
            } elseif ($sub == 'ship_' && $model->getDefaultShippingAddress()) {
                $value = substr($key, 5);
                $shipping = $model->getDefaultShippingAddress();
                $data[$key] = $shipping->getData($value);
            } else {
                $data[$key] = $model->getData($key);
            }
        }

        if (!empty($data['bill_country_id'])) {
            $country_id = $data['bill_country_id'];
            $data['bill_country_id'] = $this->getCountryName($country_id);
        }

        if (!empty($data['ship_country_id'])) {
            $country_id = $data['ship_country_id'];
            $data['ship_country_id'] = $this->getCountryName($country_id);
        }

        return $data;
    }

    /**
     * Pass data of Order to array and return mapping
     *
     * @param  \Magento\Sales\Model\Order $model
     * @param  string                     $type
     * @return array
     */
    public function getOrder($model, $type)
    {
        $this->field->setType($type);
        $magentoFields = $this->field->getMagentoFields();
        $data = [];

        foreach ($magentoFields as $key => $item) {
            $sub = substr($key, 0, 5);
            if ($sub == 'bill_' && $model->getBillingAddress()) {
                $value      = substr($key, 5);
                $billing    = $model->getBillingAddress();
                $data[$key] = $billing->getData($value);
            } elseif ($sub == 'ship_' && $model->getShippingAddress()) {
                $value = substr($key, 5);
                $shipping = $model->getShippingAddress();
                $data[$key] = $shipping->getData($value);
            } else {
                $data[$key] = $model->getData($key);
            }
        }

        return $data;
    }
}
