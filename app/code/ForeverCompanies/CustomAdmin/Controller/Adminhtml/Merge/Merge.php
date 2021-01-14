<?php

namespace ForeverCompanies\CustomAdmin\Controller\Adminhtml\Merge;

use ForeverCompanies\CustomAdmin\Helper\Data;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Result\PageFactory;

class Merge extends \Magento\Backend\App\Action
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
     * @return ResponseInterface|Redirect|ResultInterface
     */
    public function execute()
    {
        $previousUrl = $this->_redirect->getRefererUrl();
        $customerString = str_replace('customer_id/', '', stristr($previousUrl, 'customer_id'));
        $customerId = str_replace('/', '', strstr($customerString, '/', true));
        $mergedCustomerId = $this->getRequest()->getParam('id');
        if ($customerId == $mergedCustomerId) {
            $this->messageManager->addErrorMessage('You have selected the same customers');
            $redirectResult = $this->resultRedirectFactory->create();
            return $redirectResult->setPath($previousUrl);
        }
        try {
            $this->helper->mergeCustomer($mergedCustomerId, $customerId);
        } catch (NoSuchEntityException $e) {
            $this->messageManager->addErrorMessage('We can\'t merge customers - ' . $e->getMessage());
            $redirectResult = $this->resultRedirectFactory->create();
            return $redirectResult->setPath($previousUrl);
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage('We can\'t merge customers - ' . $e->getMessage());
            $redirectResult = $this->resultRedirectFactory->create();
            return $redirectResult->setPath($previousUrl);
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage('We can\'t merge customers - ' . $e->getMessage());
            $redirectResult = $this->resultRedirectFactory->create();
            return $redirectResult->setPath($previousUrl);
        }
        $this->messageManager->addSuccessMessage('Customer success merged');
        $redirectResult = $this->resultRedirectFactory->create();
        return $redirectResult->setPath('customer/index');
    }
}
