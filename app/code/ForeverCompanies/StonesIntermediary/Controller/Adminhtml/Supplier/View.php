<?php

namespace ForeverCompanies\StonesIntermediary\Controller\Adminhtml\Supplier;

use ForeverCompanies\StonesIntermediary\Api\Data\StonesSupplierInterface;
use ForeverCompanies\StonesIntermediary\Api\Data\StonesSupplierInterfaceFactory;
use ForeverCompanies\StonesIntermediary\Model\StonesSupplierManagement;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\View\Result\Layout;

class View extends Action implements HttpGetActionInterface, HttpPostActionInterface
{
    /**
     * @var StonesSupplierManagement
     */
    protected $supplierManagement;

    /**
     * @var StonesSupplierInterfaceFactory
     */
    protected $supplierInterfaceFactory;

    /**
     * @var RedirectFactory
     */
    protected $resultRedirectFactory;

    /**
     * Add constructor.
     * @param Context $context
     * @param StonesSupplierManagement $supplierManagement
     * @param StonesSupplierInterfaceFactory $supplierInterfaceFactory
     */
    public function __construct(
        Context $context,
        StonesSupplierManagement $supplierManagement,
        StonesSupplierInterfaceFactory $supplierInterfaceFactory
    ) {
        parent::__construct($context);
        $this->resultRedirectFactory = $context->getResultRedirectFactory();
        $this->supplierManagement = $supplierManagement;
        $this->supplierInterfaceFactory = $supplierInterfaceFactory;
    }

    /**
     * @return ResultInterface|Layout|void
     * @throws AlreadyExistsException
     */
    public function execute()
    {
        $post = (array) $this->getRequest()->getPost();

        if (!empty($post)) {
            $data = $this->supplierInterfaceFactory->create();
            $data->setId($post['id']);
            $data->setEmail($post['email']);
            $data->setCode($post['code']);
            $data->setName($post['name']);
            $data->setEnabled($post['enabled'] ?? 0);
            $this->supplierManagement->putStonesSupplier($data);
            $this->messageManager->addSuccessMessage('Stone Supplier updated!');
            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setPath('diamond/supplier/index');
            return $resultRedirect;
        }
        $this->_view->loadLayout();
        $this->_view->renderLayout();
    }
}
