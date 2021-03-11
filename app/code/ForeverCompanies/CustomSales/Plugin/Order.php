<?php

namespace ForeverCompanies\CustomSales\Plugin;

use Magento\Backend\Model\Auth\Session;
use Magento\Sales\Model\Order\Status\History;

class Order
{
    /**
     * @var Session
     */
    protected $session;

    /**
     * Order constructor.
     * @param Session $session
     */
    public function __construct(Session $session)
    {
        $this->session = $session;
    }

    /**
     * @param \Magento\Sales\Model\Order $subject
     * @param History $history
     */
    public function beforeAddStatusHistory(
        \Magento\Sales\Model\Order $subject,
        History $history
    ) {
        if ($this->session->getUser() !== null) {
            $history->setData('sales_person', $this->session->getUser()->getUserName());
        }
    }
}
