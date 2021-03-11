<?php

namespace DiamondNexus\Multipay\Controller\Order;

use DiamondNexus\Multipay\Logger\Logger;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;

class Paypal implements \Magento\Framework\App\Action\HttpGetActionInterface
{
    /**
     * Holds a list of errors
     *
     * @var array
     */
    protected $errors = [];

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var PageFactory
     */
    protected $pageFactory;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var ResponseInterface
     */
    protected $response;

    /**
     * @var RedirectFactory
     */
    protected $resultRedirectFactory;

    /**
     * @var ResultFactory
     */
    protected $resultFactory;

    /**
     * Paynow constructor.
     * @param Context $context
     * @param PageFactory $pageFactory
     */
    public function __construct(
        Context $context,
        PageFactory $pageFactory
    ) {
        $this->pageFactory = $pageFactory;
        $this->request = $context->getRequest();
        $this->response = $context->getResponse();
        $this->resultRedirectFactory = $context->getResultRedirectFactory();
        $this->resultFactory = $context->getResultFactory();
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
     * @return ResponseInterface|ResultInterface|Page
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('order_id');
        $page = $this->pageFactory->create();
        /** @var \DiamondNexus\Multipay\Block\Order\Paypal $block */
        $block = $page->getLayout()->getBlock('diamondnexus_paypal');
        $block->setData('order_id', $id);
        return $page;
    }

    /**
     * @return ResponseInterface|ResultInterface|Page
     */
    public function paymentAddedAction()
    {
        return $this->execute();
    }

    /**
     * @return ResponseInterface|ResultInterface|Page
     */
    public function paymentCompleteAction()
    {
        return $this->execute();
    }

    /**
     * Retrieve request object
     *
     * @return RequestInterface
     */
    public function getRequest()
    {
        return $this->request;
    }
}
