<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace ForeverCompanies\CustomSales\Helper;

use Magento\Backend\Model\Auth\Session;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\LocalizedException;
use Magento\User\Model\ResourceModel\User;

class SalesPerson extends AbstractHelper
{
    /**
     * @var User
     */
    protected $userResource;

    /**
     * @var Session
     */
    protected $session;

    /**
     * SalesPerson constructor.
     * @param Context $context
     * @param User $userResource
     * @param Session $session
     */
    public function __construct(Context $context, User $userResource, Session $session)
    {
        parent::__construct($context);
        $this->userResource = $userResource;
        $this->session = $session;
    }

    /**
     * @param $id
     * @param $attribute
     * @return string
     */
    public function getSalesPersonInfo($id, $attribute)
    {
        $connection = $this->userResource->getConnection();
        try {
            $select = $connection->select()->from($this->userResource->getMainTable())->where('user_id=:id');
        } catch (LocalizedException $e) {
            return '';
        }
        $binds = ['id' => $id];
        $person = $connection->fetchRow($select, $binds);
        if (isset($person[$attribute])) {
            return $person[$attribute];
        }
        return '';
    }

    /**
     * @return bool
     */
    public function isAllowed()
    {
        return $this->session->isAllowed('ForeverCompanies_CustomSales::sales_rep');
    }
}
