<?php

namespace DiamondNexus\Multipay\Controller\Order;

use DiamondNexus\Multipay\Logger\Logger;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Paypal extends Action
{
    /**
     * Holds a list of errors
     *
     * @var array
     */
    protected $errors = [];

    /**
     * @var PageFactory
     */
    protected $_pageFactory;

    /**
     * @var Logger
     */
    protected $logger;

    public function __construct(
        Context $context,
        PageFactory $pageFactory
    )
    {
        $this->_pageFactory = $pageFactory;
        return parent::__construct($context);
    }

    /**
     *  Sanitizes a string
     *
     * @param string|null $str
     * @return string
     */
    public function sanitize($str = null)
    {
        return preg_replace('/[^0-9A-Z\\.]/', '', $str);
    }

    /**
     * Index action method
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('order_id');
        $page = $this->_pageFactory->create();
        /** @var \DiamondNexus\Multipay\Block\Order\Paypal $block */
        $block = $page->getLayout()->getBlock('diamondnexus_paypal');
        $block->setData('order_id', $id);
        return $page;
    }

    public function paymentAddedAction()
    {
        $this->execute();
    }

    public function paymentCompleteAction()
    {
        $this->execute();
    }
}
