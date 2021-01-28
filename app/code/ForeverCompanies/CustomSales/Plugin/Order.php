<?php

namespace ForeverCompanies\CustomSales\Plugin;

use Magento\Backend\Model\Auth\Session;

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

    public function beforeAddCommentToStatusHistory(\Magento\Sales\Model\Order $subject, $comment)
    {
        if ($this->session->getUser() !== null) {
            $comment = 'Sales person: ' . $this->session->getUser()->getUserName() . '<br />' . $comment;
        }
        return $comment;
    }
}
