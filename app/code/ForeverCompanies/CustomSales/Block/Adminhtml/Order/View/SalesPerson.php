<?php

namespace ForeverCompanies\CustomSales\Block\Adminhtml\Order\View;

use Magento\Backend\Block\Template\Context;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Registry;
use Magento\Sales\Helper\Admin;
use Magento\User\Model\UserFactory;

class SalesPerson extends \Magento\Sales\Block\Adminhtml\Order\AbstractOrder
{
    /**
     * @var UserFactory
     */
    protected $userFactory;

    /**
     * SalesPerson constructor.
     * @param Context $context
     * @param Registry $registry
     * @param Admin $adminHelper
     * @param UserFactory $userFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        Admin $adminHelper,
        UserFactory $userFactory,
        array $data = []
    )
    {
        parent::__construct($context, $registry, $adminHelper, $data);
        $this->userFactory = $userFactory;
    }

    /**
     * @return string
     * @throws LocalizedException
     */
    public function getSalesPerson()
    {
        $user = false;

        // get order detail
        $salesPersonId = $this->getOrder()->getData('sales_person_id');

        if ($salesPersonId) {
            $user = $this->userFactory->create()->load($salesPersonId)->getUsername();
        }

        if ($user === false) {
            $user = "Web";
        }

        return $user;
    }

    /**
     * @return bool
     */
    public function getIsExchange()
    {
        try {
            $isExchange = $this->getOrder()->getData('is_exchange');
            if ($isExchange === "1") {
                return true;
            }
            return false;
        } catch (LocalizedException $e) {
            return false;
        }
    }

    /**
     * @return string
     */
    public function getExchangeUrl()
    {
        $params = [];
        if ($this->getRequest()->getParam('is_exchange') !== null) {
            $params['is_exchange'] = $this->getRequest()->getParam('is_exchange');
            $params['order_id'] = $this->getOrder()->getId();
        }
        return $this->getUrl('forevercompanies_custom/exchange/change', $params);
    }
}
