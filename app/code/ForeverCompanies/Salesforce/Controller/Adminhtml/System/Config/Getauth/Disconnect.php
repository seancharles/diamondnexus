<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace ForeverCompanies\Salesforce\Controller\Adminhtml\System\Config\Getauth;

use ForeverCompanies\Salesforce\Model\Connector;
use Magento\Backend\App\Action;
use Magento\Config\Model\Config as ConfigModel;

/**
 * Class ForeverCompanies
 * @package ForeverCompanies\Salesforce\Controller\Adminhtml\System\Config\Getauth
 */
class Disconnect extends Action
{
    protected $configModel;

    /**
     * Disconnect constructor.
     * @param Action\Context $context
     * @param ConfigModel $configModel
     */
    public function __construct(
        Action\Context $context,
        ConfigModel $configModel
    ) {
        parent::__construct($context);
        $this->configModel = $configModel;
    }

    public function execute()
    {
        $this->configModel->setDataByPath(Connector::XML_PATH_SALESFORCE_IS_CONNECTED, 0);
        $this->configModel->save();
        $this->configModel->setDataByPath(Connector::XML_PATH_SALESFORCE_ACCESS_TOKEN, null);
        $this->configModel->save();
        $this->configModel->setDataByPath(Connector::XML_PATH_SALESFORCE_INSTANCE_URL, null);
        $this->configModel->save();
    }
}
