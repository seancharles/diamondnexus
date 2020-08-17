<?php

/**
 * Copyright (c) 2018, Prog Leasing, LLC
 * Licensed under the Open Software License version 3.0
 */

namespace Progressive\PayWithProgressive\Controller\Index;

use \Magento\Framework\App\Action\Action;

class Index extends Action{
    protected $resultPageFactory;

    public function __construct(\Magento\Framework\App\Action\Context $context,
                                \Magento\Framework\View\Result\PageFactory $resultPageFactory)
    {
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        $this->getResponse()->setHeader('Content-type', 'application/json');
        $this->getResponse()->setBody('{ "test": "test" }');
        
    }
}