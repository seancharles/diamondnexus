<?php
/**
 * Copyright (c) 2019 Prog Leasing, LLC. Copyright (c) 2018, Prog Leasing, LLC.
 */
namespace Progressive\PayWithProgressive\Controller\Payment;

class Confirm extends \Magento\Framework\App\Action\Action
{
        protected $_pageFactory;
        public function __construct(
                \Magento\Framework\App\Action\Context $context,
                \Magento\Framework\View\Result\PageFactory $pageFactory)
        {
                $this->_pageFactory = $pageFactory;
                return parent::__construct($context);
        }
        public function execute()
        {
            return $this->_pageFactory->create();
        }
}


