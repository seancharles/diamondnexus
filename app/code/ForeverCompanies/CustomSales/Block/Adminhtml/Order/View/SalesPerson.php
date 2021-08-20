<?php

namespace ForeverCompanies\CustomSales\Block\Adminhtml\Order\View;

use Magento\Backend\Block\Template\Context;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Registry;
use Magento\Sales\Helper\Admin;
use Magento\User\Model\UserFactory;
use Magento\Backend\Model\Session as AdminSession;

class SalesPerson extends \Magento\Sales\Block\Adminhtml\Order\AbstractOrder
{
    /**
     * @var UserFactory
     */
    protected $userFactory;
    protected $session;

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
        AdminSession $adminS,
        array $data = []
    ) {
        $this->userFactory = $userFactory;
        $this->session = $adminS;
        parent::__construct($context, $registry, $adminHelper, $data);
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
            $user = $this->userFactory->create()->load($salesPersonId)->getName();
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
        
        if ($this->session->getIsExchange() == 1) {
            $this->session->setOrderId($this->getOrder()->getId());
        } else {
            $this->session->unsIsExchange();
            $this->session->unsOrderId();
        }
        return $this->getUrl('forevercompanies_custom/exchange/change');
    }
}
