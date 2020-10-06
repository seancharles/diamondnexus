<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace ForeverCompanies\Salesforce\Model;

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
     * @param MapFactory $map
     */
    public function __construct(
        MapFactory $map
    ) {
        $this->mapFactory = $map;
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
}
