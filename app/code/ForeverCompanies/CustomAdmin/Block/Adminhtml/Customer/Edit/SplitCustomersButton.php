<?php

namespace ForeverCompanies\CustomAdmin\Block\Adminhtml\Customer\Edit;

use Magento\Customer\Block\Adminhtml\Edit\GenericButton;
use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

class SplitCustomersButton extends GenericButton implements ButtonProviderInterface
{
    /**
     * @return array
     */
    public function getButtonData()
    {
        return [
            'label' => __('Split Customers'),
            'on_click' => sprintf("location.href = '%s';", $this->getMergeCustomersUrl()),
            'class' => 'add',
            'sort_order' => 42,
        ];
    }

    /**
     * @return string
     */
    protected function getMergeCustomersUrl()
    {
        return $this->getUrl('customadmin/split/index', ['customer_id' => $this->getCustomerId()]);
    }
}
