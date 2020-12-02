<?php

namespace ForeverCompanies\CustomSales\Block\Adminhtml\Order\View;

use ForeverCompanies\CustomSales\Helper\SalesPerson;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Registry;
use Magento\Sales\Block\Adminhtml\Order\AbstractOrder;
use Magento\Sales\Helper\Admin;

class LoggedUser extends AbstractOrder
{
    /**
     * @var SalesPerson
     */
    protected $salesPersonHelper;

    /**
     * LoggedUser constructor.
     * @param Context $context
     * @param Registry $registry
     * @param Admin $adminHelper
     * @param SalesPerson $salesPersonHelper
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        Admin $adminHelper,
        SalesPerson $salesPersonHelper,
        array $data = []
    ) {
        parent::__construct($context, $registry, $adminHelper, $data);
        $this->salesPersonHelper = $salesPersonHelper;
    }

    /**
     * @return string
     */
    public function getLoggedUser()
    {
        try {
            return $this->getOrder()->getData('loggeduser') ?? 'not edited';
        } catch (LocalizedException $e) {
            $this->_logger->error($e->getMessage());
            return '';
        }
    }

    /**
     * @return string
     */
    public function getSalesPerson()
    {
        try {
            $salesPersonId = $this->getOrder()->getData('sales_person_id');
            return $this->salesPersonHelper->getSalesPersonInfo($salesPersonId, 'username');
        } catch (LocalizedException $e) {
            return '';
        }
    }

    /**
     * @return bool
     */
    public function isAllowed()
    {
        return $this->salesPersonHelper->isAllowed();
    }

    /**
     * @return string
     */
    public function getFormUrl()
    {
        $orderId = $this->getRequest()->getParam('order_id');
        return $this->getUrl('forevercompanies_custom/order/edit', ['order_id' => $orderId]);
    }

    /**
     * @return string
     */
    public function getOrderId()
    {
        try {
            return $this->getOrder()->getId();
        } catch (LocalizedException $e) {
            return '';
        }
    }
}
