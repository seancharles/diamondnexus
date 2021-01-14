<?php

namespace ForeverCompanies\CustomAttributes\Plugin\Framework\Model\ActionValidator;

use Magento\Catalog\Model\Product;

class RemoveAction
{
    /**
     * @param \Magento\Framework\Model\ActionValidator\RemoveAction $subject
     * @param $model
     * @param $isAllowed
     * @return mixed
     * @para9m $isAllowed
     */
    public function afterIsAllowed(
        \Magento\Framework\Model\ActionValidator\RemoveAction $subject,
        bool $isAllowed,
        $model
    ) {
        if ($model instanceof Product && $model->getData('dev_tag') !== null) {
            return true;
        }
        return $isAllowed;
    }
}
