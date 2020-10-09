<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace ForeverCompanies\Salesforce\Controller\Adminhtml\Map;
use ForeverCompanies\Salesforce\Controller\Adminhtml\Map as MapController;


/**
 * Class Index Controller
 *
 * @package ForeverCompanies\Salesforce\Controller\Adminhtml\Map
 */
class Index extends MapController
{
    /**
     * execute the action
     *
     * @return
     *  \Magento\Backend\Model\View\Result\Page|\Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $this->_setPageData();
        return $this->getResultPage();
    }

    /**
     * Instantiate result page object
     *
     * @return
     *  \Magento\Backend\Model\View\Result\Page|\Magento\Framework\View\Result\Page
     */
    public function getResultPage()
    {
        if (is_null($this->resultPage)){
            $this->resultPage = $this->resultPageFactory->create();
        }

        return $this->resultPage;
    }

    /**
     * Set page data
     *
     * @return $this
     */
    protected function _setPageData()
    {
        $resultPage = $this->getResultPage();
        $resultPage->getConfig()->getTitle()->prepend((__('Manage Mapping')));
        return $this;
    }
}
