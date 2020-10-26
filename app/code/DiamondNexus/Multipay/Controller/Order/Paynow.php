<?php
namespace DiamondNexus\Multipay\Controller\Order;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Paynow extends Action
{
    /**
     * @var PageFactory
     */
    protected $_pageFactory;

    public function __construct(
        Context $context,
        PageFactory $pageFactory
    ) {
        $this->_pageFactory = $pageFactory;
        return parent::__construct($context);
    }

    public function execute()
    {
        $id = $this->getRequest()->getParam('order_id');

        $page = $this->_pageFactory->create();
        /** @var \DiamondNexus\Multipay\Block\Order\Paynow $block */
        $block = $page->getLayout()->getBlock('diamondnexus_paynow');
        $block->setData('order_id', $id);
        return $page;
    }
}
