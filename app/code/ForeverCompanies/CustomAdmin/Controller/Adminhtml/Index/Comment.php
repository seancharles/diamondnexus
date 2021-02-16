<?php

namespace ForeverCompanies\CustomAdmin\Controller\Adminhtml\Index;

use Magento\Customer\Controller\Adminhtml\Index;
use Magento\Framework\View\Result\Layout;

class Comment extends Index
{
    /**
     * Customer compare grid
     *
     * @return Layout
     */
    public function execute()
    {

        $this->initCurrentCustomer();
        return $this->resultLayoutFactory->create();
    }
}
