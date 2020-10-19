<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace ForeverCompanies\Salesforce\Controller\Adminhtml\Field;

use Magento\Backend\App\Action;
use ForeverCompanies\Salesforce\Model\FieldFactory;

/**
 * Class UpdateAllFields
 *
 * @package ForeverCompanies\Salesforce\Controller\Adminhtml\Field
 */

class UpdateAllFields extends Action
{
    /**
     * @var \ForeverCompanies\Salesforce\Model\FieldFactory
     */
    protected $fieldFactory;


    /**
     * @param FieldFactory $fieldFactory
     */
    public function __construct(
        FieldFactory $fieldFactory
    ) {
        $this->fieldFactory = $fieldFactory;
    }

    /**
     * Execute
     */
    public function execute()
    {
        $data = $this->getRequest()->getPostValue();
        if ($data){
            $model = $this->fieldFactory->create();
            $table = $model->getAllTable();
            foreach ($table as $s_table => $m_table) {
                $model = $this->fieldFactory->create();
                $model->loadByTable($s_table, true);
            }
        }

        return;
    }
}
