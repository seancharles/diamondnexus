<?php

namespace ForeverCompanies\CronControls\Controller\Index;

use Psr\Log\LoggerInterface;
use Magento\Framework\App\Action\Context;

class Index extends \Magento\Framework\App\Action\Action
{
    public function __construct(
        Context $context
    ) { 
        return parent::__construct($context);
    }
    
    public function execute()
    {
        echo 'fdsfds';die;
    }
}