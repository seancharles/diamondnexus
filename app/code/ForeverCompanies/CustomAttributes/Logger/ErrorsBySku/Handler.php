<?php
namespace ForeverCompanies\CustomAttributes\Logger\ErrorsBySku;

use Monolog\Logger;

class Handler extends \Magento\Framework\Logger\Handler\Base
{
    /**
     * Logging level
     * @var int
     */
    protected $loggerType = Logger::DEBUG;

    /**
     * File name
     * @var string
     */
    protected $fileName = '/var/log/forevercompanies_options_errors_by_sku.log';
}
