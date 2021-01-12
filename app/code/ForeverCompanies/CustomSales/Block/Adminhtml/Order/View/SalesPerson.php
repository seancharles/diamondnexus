<?php

namespace ForeverCompanies\CustomSales\Block\Adminhtml\Order\View;

use Magento\Backend\Block\Template\Context;
use Magento\Framework\Registry;
use Magento\Sales\Helper\Admin;
use Magento\User\Model\UserFactory;

class SalesPerson extends \Magento\Sales\Block\Adminhtml\Order\AbstractOrder
{
    /**
     * SalesPerson constructor.
     * @param Context $context
     * @param Registry $registry
     * @param Admin $adminHelper
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        Admin $adminHelper,
        UserFactory $userFactory,
        array $data = []
    ) {
        parent::__construct($context, $registry, $adminHelper, $data);
        $this->userFactory = $userFactory;
    }
    
    /**
     * @return string
     */
    public function getSalesPerson()
    {
        $user = false;
        
        // get order detail
        $salesPersonId = $this->getOrder()->getData('sales_person_id');
        
        if($salesPersonId) {
            $user = $this->userFactory->create()->load($salesPersonId)->getUsername();
        }
        
        if($user === false) {
            $user = "Web";
        }
        
        return $user;
    }
}
