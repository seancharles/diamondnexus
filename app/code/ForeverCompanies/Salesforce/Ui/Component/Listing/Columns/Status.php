<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace ForeverCompanies\Salesforce\Ui\Component\Listing\Columns;

use Magento\Ui\Component\Listing\Columns\Column;
use ForeverCompanies\Salesforce\Model\Report\Status as LogStatus;

/**
 * Class Status
 * @package ForeverCompanies\Salesforce\Ui\Component\Listing\Columns
 */
class Status extends Column
{
    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])){
            foreach ($dataSource['data']['items'] as &$item){
                if ($item['status'] && $item['status'] == LogStatus::SUCCESS_STATUS){
                    $class = 'notice';
                    $label = 'Success';
                } else {
                    $class = 'critical';
                    $label = 'Error';
                }
                $item['status'] = '<span class="grid-severity-'
                    . $class .'">'. $label .'</span>';
            }
        }
        return $dataSource;
    }
}
