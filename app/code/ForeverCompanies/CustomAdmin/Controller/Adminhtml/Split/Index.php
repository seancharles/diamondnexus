<?php

namespace ForeverCompanies\CustomAdmin\Controller\Adminhtml\Split;

use ForeverCompanies\CustomAdmin\Helper\Data;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Result\PageFactory;

class Index extends \Magento\Backend\App\Action
{

    protected $resultPageFactory = false;

    /**
     * @var Context
     */
    private $context;

    /**
     * @var Data
     */
    protected $helper;

    /**
     * Index constructor.
     * @param PageFactory $resultPageFactory
     * @param Context $context
     * @param Data $helper
     */
    public function __construct(
        PageFactory $resultPageFactory,
        Context $context,
        Data $helper
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->context = $context;
        $this->helper = $helper;
    }

    /**
     * @return mixed
     */
    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->prepend(__('Choose data for new customer'));
        return $resultPage;
    }
}
