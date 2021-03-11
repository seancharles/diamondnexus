<?php

namespace ForeverCompanies\CustomSales\Block\Adminhtml\Order\Create;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Model\Auth\Session;
use Magento\Backend\Model\Session\Quote;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Quote\Model\QuoteRepository;
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
     * @var QuoteRepository
     */
    protected $quoteRepository;

    /**
     * SalesPerson constructor.
     * @param Context $context
     * @param Quote $sessionQuote
     * @param QuoteRepository $quoteRepository
     * @param Create $orderCreate
     * @param PriceCurrencyInterface $priceCurrency
     * @param Session $authSession
     * @param \ForeverCompanies\CustomSales\Helper\SalesPerson $salesPersonHelper
     * @param array $data
     */
    public function __construct(
        Context $context,
        Quote $sessionQuote,
        QuoteRepository $quoteRepository,
        Create $orderCreate,
        PriceCurrencyInterface $priceCurrency,
        Session $authSession,
        \ForeverCompanies\CustomSales\Helper\SalesPerson $salesPersonHelper,
        array $data = []
    ) {
        parent::__construct($context, $sessionQuote, $orderCreate, $priceCurrency, $data);
        $this->authSession = $authSession;
        $this->salesPersonHelper = $salesPersonHelper;
        $this->quoteRepository = $quoteRepository;
    }

    /**
     * @return string|null
     */
    public function getSalesPerson()
    {
        $salesPersonId = null;
        if ($this->getQuote()->getData('sales_person_id') !== null) {
            $salesPersonId = $this->getQuote()->getData('sales_person_id');
        }
        if ($this->getRequest()->getParam('sales_person_id') !== null) {
            $salesPersonId = $this->getRequest()->getParam('sales_person_id');
        }
        if ($salesPersonId !== null) {
            $firstname = $this->salesPersonHelper->getSalesPersonInfo($salesPersonId, 'firstname');
            $lastname = $this->salesPersonHelper->getSalesPersonInfo($salesPersonId, 'lastname');
            if ($firstname == '' && $lastname == '') {
                $quote = $this->getQuote()->setData('sales_person_id', $this->authSession->getUser()->getId());
                $this->quoteRepository->save($quote);
                return $this->authSession->getUser()->getName();
            }
            return $firstname . ' ' . $lastname;
        }
        return $this->authSession->getUser()->getName();
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
     * @return string
     */
    public function getExchangeUrl()
    {
        $params = [];
        if ($this->getRequest()->getParam('is_exchange') !== null) {
            $params['is_exchange'] = $this->getRequest()->getParam('is_exchange');
        }
        return $this->getUrl('forevercompanies_custom/exchange/start', $params);
    }

    /**
     * @return bool
     */
    public function isAllowed()
    {
        return $this->salesPersonHelper->isAllowed();
    }

    /**
     * @return bool
     */
    public function getIsExchange()
    {
        $isExchange = $this->getRequest()->getParam('is_exchange');
        if ($isExchange === "1") {
            return true;
        }
        return false;
    }
}
