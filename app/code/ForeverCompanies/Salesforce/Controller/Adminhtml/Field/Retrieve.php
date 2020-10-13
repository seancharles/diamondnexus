<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace ForeverCompanies\Salesforce\Controller\Adminhtml\Field;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use ForeverCompanies\Salesforce\Model\FieldFactory;

/**
 * Class Retrieve
 *
 * @package ForeverCompanies\Salesforce\Controller\Adminhtml\Field
 */
class Retrieve extends Action
{
    /**
     * @var \ForeverCompanies\Salesforce\Model\FieldFactory
     */
    protected $fieldFactory;

    /**
     * @param \Magento\Backend\App\Action\Context  $context
     * @param \ForeverCompanies\Salesforce\Model\FieldFactory $fieldFactory
     */
    public function __construct(
        Context $context,
        FieldFactory $fieldFactory
    ) {
        parent::__construct($context);
        $this->fieldFactory = $fieldFactory;
    }

    /**
     * Execute
     */
    public function execute()
    {
        $data = $this->getRequest()->getPostValue();
        if ($data){
            $type = $data['type'];
            $out = [];
            $out['salesforce_options'] = '';
            $out['magento_options'] = '';
            if ($type) {
                $model = $this->fieldFactory->create();
                $model->loadByTable($type);
                $magentoFields = $model->getMagentoFields();

                $magentoOption = '';

                if ($magentoFields){
                    foreach ($magentoFields as $value => $label) {
                        $magentoOption .= "<option value ='$value' >".$label."</option>";
                    }
                }

                $out['magento_options'] = $magentoOption;
                $salesforceFields       = $model->getSalesforceFields();
                $salesforceOption       = '';

                if ($salesforceFields){
                    foreach ($salesforceFields as $value => $label){
                        $salesforceOption .= "<option value ='$value' >".$label."</option>";
                    }
                }

                $out['salesforce_options'] = $salesforceOption;
            }
            echo json_encode($out);
        }
    }
}
