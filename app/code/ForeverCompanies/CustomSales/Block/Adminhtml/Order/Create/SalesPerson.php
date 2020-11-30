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
     * @var User
     */
    protected $userResource;

    /**
     * SalesPerson constructor.
     * @param Context $context
     * @param Quote $sessionQuote
     * @param Create $orderCreate
     * @param PriceCurrencyInterface $priceCurrency
     * @param Session $authSession
     * @param User $userResource
     * @param array $data
     */
    public function __construct(
        Context $context,
        Quote $sessionQuote,
        Create $orderCreate,
        PriceCurrencyInterface $priceCurrency,
        Session $authSession,
        User $userResource,
        array $data = []
    )
    {
        parent::__construct($context, $sessionQuote, $orderCreate, $priceCurrency, $data);
        $this->authSession = $authSession;
        $this->userResource = $userResource;
    }

    /**
     * @return mixed|string|null
     */
    public function getSalesPerson()
    {
        $salesPersonId = $this->getRequest()->getParam('sales_person_id');
        if ($salesPersonId !== null) {
            $connection = $this->userResource->getConnection();
            try {
                $select = $connection->select()->from($this->userResource->getMainTable())->where('user_id=:id');
            } catch (LocalizedException $e) {
                return '';
            }
            $binds = ['id' => $salesPersonId];
            return $connection->fetchRow($select, $binds)['username'];
        }
        return $this->authSession->getUser()->getUserName();
    }

    /**
     * @return string
     */
    public function getFormUrl()
    {
        return $this->getUrl('forevercompanies_custom/order/user', [
            'quote' => $this->getQuote()
        ]);
    }

    /**
     * @return bool
     */
    public function isAllowed()
    {
        return $this->authSession->isAllowed('ForeverCompanies_CustomSales::sales_rep');
    }
}
