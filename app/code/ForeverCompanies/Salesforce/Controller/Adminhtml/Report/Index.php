<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace ForeverCompanies\Salesforce\Controller\Adminhtml\Request;

use ForeverCompanies\Salesforce\Controller\Adminhtml\Report as ReportController;

/**
 * Class Index
 *
 * @package ForeverCompanies\Salesforce\Controller\Adminhtml\Report
 */
class Index extends ReportController
{
    /**
     * execute the action
     *
     * @return \Magento\Backend\Model\View\Result\Page|\Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $this->_setPageData();
        return $this->getResultPage();
    }
}
