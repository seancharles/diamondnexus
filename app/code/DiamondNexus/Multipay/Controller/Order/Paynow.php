<?php
namespace DiamondNexus\Multipay\Controller\Order;

use Magento\Backend\App\Action\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;

class Paynow implements \Magento\Framework\App\Action\HttpGetActionInterface
{
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
     * @return ResponseInterface|ResultInterface|Page
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('order_id');

        $page = $this->pageFactory->create();
        /** @var \DiamondNexus\Multipay\Block\Order\Paynow $block */
        $block = $page->getLayout()->getBlock('diamondnexus_paynow');
        $block->setData('order_id', $id);
        return $page;
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
