<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

class Edit extends \ForeverCompanies\Promotions\Controller\Adminhtml\Promo\Rule
{
    /**
     * Rule edit action
     *
     * @return void
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');

        /**
         * @var \ForeverCompanies\Promotions\Model\Rule $model
         */
        $model = $this->ruleFactory->create();

        if ($id){
            $model->load($id);
            if (!$model->getRuleId()){
                $this->messageManager->addErrorMessage(__('This rule no longer exists.'));
                $this->_redirect('forevercompanies_rules/*/');
                return;
            }
        }

        // set entered data if was error when we do save
        $data = $this->_session->getPageData(true);
        if (!empty($data)){
            $model->addData($data);
        }

        $model->getConditions()->setJsFormObject('rule_conditions_fieldset');

        $this->coreRegistry->register('current_rule', $model);

        $this->_initAction();
        $this->_view->getLayout()
            ->getBlock('forevercompanies_rule_edit')
            ->setData('action', $this->getUrl('forevercompanies_rules/*/save'));

        $this->_addBreadcrumb($id ? __('Edit Rule') : __('New Rule'), $id ? __('Edit Rule') : __('New Rule'));

        $this->_view->getPage()->getConfig()->getTitle()->prepend(
            $model->getRuleId() ? $model->getName() : __('New Rule')
        );
        $this->_view->renderLayout();

    }
}
