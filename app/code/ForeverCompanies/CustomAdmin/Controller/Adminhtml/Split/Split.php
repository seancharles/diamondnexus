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

class Split extends \Magento\Backend\App\Action
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
        Context $context,
        Data $helper
    ) {
        parent::__construct($context);
        $this->context = $context;
        $this->helper = $helper;
    }

    /**
     * @return ResponseInterface|Redirect|ResultInterface
     */
    public function execute()
    {
        $previousUrl = $this->_redirect->getRefererUrl();
        try {
            $this->helper->splitCustomer($this->getRequest()->getParams());
        } catch (NoSuchEntityException $e) {
            $this->messageManager->addErrorMessage('We can\'t split customers - ' . $e->getMessage());
            $redirectResult = $this->resultRedirectFactory->create();
            return $redirectResult->setPath($previousUrl);
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage('We can\'t split customers - ' . $e->getMessage());
            $redirectResult = $this->resultRedirectFactory->create();
            return $redirectResult->setPath($previousUrl);
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage('We can\'t split customers - ' . $e->getMessage());
            $redirectResult = $this->resultRedirectFactory->create();
            return $redirectResult->setPath($previousUrl);
        }
        $this->messageManager->addSuccessMessage('Customer success split');
        $redirectResult = $this->resultRedirectFactory->create();
        return $redirectResult->setPath('customer/index');
    }
}
