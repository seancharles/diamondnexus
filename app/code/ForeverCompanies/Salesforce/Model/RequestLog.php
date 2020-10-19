<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace ForeverCompanies\Salesforce\Model;

use Magento\Framework\Model\AbstractModel;

class RequestLog extends AbstractModel
{
    const REST_REQUEST_TYPE = 'rest';
    const BULK_REQUEST_TYPE = 'bulk';

    protected function _construct()
    {
        $this->_init('ForeverCompanies\Salesforce\Model\ResourceModel\RequestLog');
    }

    public function addRequest($type)
    {
        $type = strtolower($type);
        $column = $type . '_request';

        /** @var \ForeverCompanies\Salesforce\Model\RequestLog $request */

        $request = $this->getCollection()
            ->addFieldToSelect('*')
            ->addFieldToFilter('date', date('Y-m-d'))
            ->getLastItem();
        if (!$request->getId()){
            $this->setData('date', date('Y-m-d'));
            $this->setData($column, 1);
            $this->save();
        } else {
            $requestCount = $request->getData($column) + 1;
            $request->setData($column, $requestCount);
            $request->save();
        }
    }
}
