<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace ForeverCompanies\Salesforce\Controller\Adminhtml\System\Config\Getauth;

use ForeverCompanies\Salesforce\Model\Connector;
use Magento\Backend\App\Action;

/**
 * Class ForeverCompanies
 * @package ForeverCompanies\Salesforce\Controller\Adminhtml\System\Config\Getauth
 */
class GetAuth extends Action
{
    const ERROR_CONNECT_TO_SALESFORCECRM = 'INVALID_PASSWORD';

    /**
     * @var \ForeverCompanies\Salesforce\Model\Connector
     */
    protected $connector;

    /**
     * GetAuth constructor.
     * @param Action\Context $context
     * @param Connector $connector
     */
    public function __construct(
        Action\Context $context,
        Connector $connector
    ) {
        parent::__construct($context);
        $this->connector   = $connector;
    }

    /**
     * Check whether vat is valid
     *
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        $data = $this->getRequest()->getPostValue();
        if ($data) {
            if (empty($data['username']) ||
                empty($data['password']) ||
                empty($data['client_id']) ||
                empty($data['client_secret'])) {
                $result['error']       = 1;
                $result['description'] = "Please enter all information";
                echo json_encode($result);
                return;
            }

            $response = $this->connector->getAccessToken($data, true);
            if (!empty($response['error'])) {
                $result['error'] = 1;
                $result['description'] = $response['error_description'];
                echo json_encode($result);
                return;
            } else {
                $result = $response;
                $result['error'] = 0;
                echo json_encode($result);
                return;
            }
        }
    }
}
