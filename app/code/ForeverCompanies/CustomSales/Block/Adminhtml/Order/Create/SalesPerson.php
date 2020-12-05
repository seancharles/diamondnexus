<?php

namespace ForeverCompanies\CustomSales\Block\Adminhtml\Order\Create;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Model\Auth\Session;
use Magento\Backend\Model\Session\Quote;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Sales\Model\AdminOrder\Create;
use Magento\User\Model\ResourceModel\User;
use Magento\User\Model\UserFactory;

class SalesPerson extends \Magento\Sales\Block\Adminhtml\Order\Create\AbstractCreate
{
    /**
     * @var Session
     */
    protected $authSession;

    /**
     * @var \ForeverCompanies\CustomSales\Helper\SalesPerson
     */
    protected $salesPersonHelper;

    /**
     * SalesPerson constructor.
     * @param Context $context
     * @param Quote $sessionQuote
     * @param Create $orderCreate
     * @param PriceCurrencyInterface $priceCurrency
     * @param Session $authSession
     * @param \ForeverCompanies\CustomSales\Helper\SalesPerson $salesPersonHelper
     * @param array $data
     */
    public function __construct(
        Context $context,
        Quote $sessionQuote,
        Create $orderCreate,
        PriceCurrencyInterface $priceCurrency,
        Session $authSession,
        \ForeverCompanies\CustomSales\Helper\SalesPerson $salesPersonHelper,
        array $data = []
    ) {
        parent::__construct($context, $sessionQuote, $orderCreate, $priceCurrency, $data);
        $this->authSession = $authSession;
        $this->salesPersonHelper = $salesPersonHelper;
    }

    /**
     * @return mixed|string|null
     */
    public function getSalesPerson()
    {
        $salesPersonId = $this->getRequest()->getParam('sales_person_id');
        if ($salesPersonId !== null) {
            return $this->salesPersonHelper->getSalesPersonInfo($salesPersonId, 'username');
        }
        return $this->authSession->getUser()->getUserName();
    }

    /**
     * @return string
     */
    public function getFormUrl()
    {
        $params = [];
        if ($this->getRequest()->getParam('status') !== null) {
            $params['status'] = $this->getRequest()->getParam('status');
        }
        return $this->getUrl('forevercompanies_custom/order/user', $params);
    }

    /**
     * @return bool
     */
    public function isAllowed()
    {
        return $this->salesPersonHelper->isAllowed();
    }
}
