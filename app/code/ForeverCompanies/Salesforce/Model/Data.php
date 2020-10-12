<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace ForeverCompanies\Salesforce\Model;

use Magento\Directory\Model\Country;
use Magento\Tests\NamingConvention\true\string;

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
     * Select mapping
     *
     * @param string $data
     * @param string $type
     * @return array
     */
    public function getMapping($data, $type){

        $model = $this->mapFactory->create();
        $collection = $model->getResourceCollection()
            ->addFieldToFilter('type', $type)
            ->addFieldToFilter('status', 1);
        $map = [];
        $result = [];

        /** @var Map $value */
        foreach ($collection as $key => $value){
            $salesforce  = $value->getSalesforce();
            $magento = $value->getMagento();
            $map[$salesforce] = $magento;
        }

        /** @var string $value */
        foreach($map as $key => $value){
            if ($data[$value]){
                $result[$key] = $data[$value];
            }
        }
        return $result;
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

        foreach ($magentoFields as $key => $item){
            $sub = substr($key, 0 , 5);
            if ($sub == 'bill_'){
                $billing = $model->getBillingAddress();
                $data[$key] = $billing->getData(substr($key,5));
            } elseif($sub == 'ship_'){
                $shipping = $model->getShippingAddress();
            } else {
                $data[$key] = $model->getData($key);
            }
        }

        if (!empty($data['bill_country_id'])){
            $country_id = $data['bill_country_id'];
            $data['bill_country_id'] = $this->getCountryName($country_id);
        }

        if (!empty($data['ship_country_id'])){
            $country_id = $data['ship_country_id'];
            $data['ship_country_id'] = $this->getCountryName($country_id);
        }

        // Mapping data
        $params = $this->getMapping($data, $type);
        return $params;

    }
}
