<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace ForeverCompanies\Salesforce\Controller\Adminhtml\Map;

use ForeverCompanies\Salesforce\Controller\Adminhtml\Map as MapController;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;
use ForeverCompanies\Salesforce\Model\MapFactory;
use ForeverCompanies\Salesforce\Model\ResourceModel\Map\CollectionFactory as MapCollectionFactory;
use Magento\Framework\View\Result\PageFactory;
use Magento\Backend\Model\View\Result\ForwardFactory;

/**
 * Class Order
 * @package ForeverCompanies\Salesforce\Controller\Adminhtml\Map
 */
class Edit extends MapController
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @param Context              $context
     * @param Registry             $coreRegistry
     * @param MapFactory           $mapFactory
     * @param PageFactory          $resultPageFactory
     * @param ForwardFactory       $resultForwardFactory
     * @param MapCollectionFactory $collectionFactory
     */
    public function __construct(
        Context $context,
        Registry $coreRegistry,
        MapFactory $mapFactory,
        PageFactory $resultPageFactory,
        ForwardFactory $resultForwardFactory,
        MapCollectionFactory $collectionFactory
    ) {
        $this->coreRegistry = $coreRegistry;
        $this->resultForwardFactory = $resultForwardFactory;
        parent::__construct($context, $coreRegistry, $resultPageFactory, $mapFactory, $collectionFactory);
    }

    /**
     * Edit Mapping
     *
     * @return \Magento\Backend\Model\View\Result\Page|\Magento\Backend\Model\View\Result\Redirect
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        $model = $this->mapFactory->create();
        if ($id) {
            $model->load($id);
            if (!$model->getId()) {
                $this->messageManager->addError(__('This mapping no longer exists.'));
            }
            $resultRedirect = $this->resultPageFactory->create();
            return $resultRedirect->setPath('*/*/');
        }


        $data = $this->_objectManager->get('Magento\Backend\Model\Session')->getFormData(true);
        if (!empty($data)) {
            $model->setData($data);
        }

        $this->coreRegistry->register('mapping', $model);

        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->_initAction();
        $resultPage->getConfig()->getTitle()
            ->prepend($model->getId() ? __('Edit Mapping %1', $model->getType()) : __('New Mapping'));
        return $resultPage;
    }
}
