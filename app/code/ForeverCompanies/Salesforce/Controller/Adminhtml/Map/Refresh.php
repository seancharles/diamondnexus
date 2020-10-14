<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magenest\Salesforce\Controller\Adminhtml\Map;

use Magento\Backend\App\Action\Context;
use Magento\Cms\Model\PageFactory;
use Magento\Backend\App\Action;
use ForeverCompanies\Salesforce\Model\Connector;

class Refresh extends Action
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var \ForeverCompanies\Salesforce\Model\Connector
     */
    protected $connector;

    /**
     * @param Context     $context
     * @param PageFactory $resultPageFactory
     * @param Connector   $connector
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        Connector $connector
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->connector        = $connector;
    }

    /**
     * execute
     */
    public function execute()
    {
        $response = $this->connector->getAccessToken();

        if (!empty($response['access_token'])){
            $this->messageManager->addSuccess('Refresh access token of SalesforceCRM success !');
            $this->getResponse()->setRedirect($this->_redirect->getRedirectUrl($this->getUrl('*')));
            return;
        } else {
            $this->messageManager->addError('Can\'t refesh access token, please check in configuration!');
        }
        $this->_redirect('adminhtml/*/');
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return true;
    }
}
