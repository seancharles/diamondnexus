<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace ForeverCompanies\Promotions\Controller\Adminhtml\Promo\Rule;

class NewConditionHtml extends \ForeverCompanies\Promotions\Controller\Adminhtml\Promo\Rule
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'ForeverCompanies_Promotions::promotions';

    /**
     * New condition html action
     *
     * @return void
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        $typeArr = explode('|', str_replace('-', '/', $this->getRequest()->getParam('type')));
        $type = $typeArr[0];

        $model = $this->_objectManager->create(
                $type
            )->setId(
                $id
            )->setType(
                $type
            )->setRule(
                $this->ruleFactory->create()
            )->setPrefix(
                'conditions'
            );
        if (!empty($typeArr[1])){
            $model->setAttribute($typeArr[1]);
        }

        if ($model instanceof \Magento\Rule\Model\Condition\AbstractCondition) {
            $model->setJsFormObject($this->getRequest()->getParam('form'));
            $html = $model->asHtmlRecursive();
        } else {
            $html = '';
        }
        $this->getResponse()->setBody($html);
    }
}
