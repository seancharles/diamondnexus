<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace ForeverCompanies\Salesforce\Controller\Adminhtml\Map;

use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;
use ForeverCompanies\Salesforce\Model\MapFactory;
use Magento\Framework\View\Result\PageFactory;
use ForeverCompanies\Salesforce\Model\ResourceModel\Map\CollectionFactory as MapCollectionFactory;
use ForeverCompanies\Salesforce\Controller\Adminhtml\Map as MapController;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class Save
 *
 * @package ForeverCompanies\Salesforce\Controller\Adminhtml\Map
 */
class Save extends MapController
{
    /**
     * @param Context              $context
     * @param Registry             $coreRegistry
     * @param PageFactory          $resultPageFactory
     * @param MapFactory           $mapFactory
     * @param MapCollectionFactory $collectionFactory
     */
    public function __construct(
        Context $context,
        Registry $coreRegistry,
        PageFactory $resultPageFactory,
        MapFactory  $mapFactory,
        MapCollectionFactory $collectionFactory
    ) {
        parent::__construct(
            $context,
            $coreRegistry,
            $resultPageFactory,
            $mapFactory,
            $collectionFactory
        );
    }

    /**
     * Execute action
     *
     * @return $this
     * @throws LocalizedException
     */
    public function execute()
    {
        $data = $this->getRequest()->getPostValue();
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($data) {
            $model = $this->mapFactory->create();
            $id = $this->getRequest()->getParam('id');
            if ($id) {
                $model->load($id);
                if ($id != $model->getId()) {
                    throw new LocalizedException(__('Wrong mapping rule.'));
                }
            }

            $model->setData($data);
            $this->_objectManager->get('Magento\Backend\Model\Session')->setPageData($model->getData());
            try {
                $model->save();
                $this->messageManager->addSuccess(__('The mapping has been saved.'));
                $this->_objectManager->get('Magento\Backend\Model\Session')
                    ->setPageData(false);
                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath('*/*/edit', ['id' => $model->getId(), '_current' => true]);
                }

                return $resultRedirect->setPath('*/*/');
            } catch (LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addError($e, __('Something went wrong while saving the mapping.'));
                $this->_objectManager->get('Psr\Log\LoggerInterface')->critical($e);
                $this->_objectManager->get('Magento\Backend\Model\Session')->setPageData($data);
                return $resultRedirect->setPath('*/*/edit', ['id' => $this->getRequest()->getParam('id')]);
            }
        }

        return $resultRedirect->setPath('*/*/');
    }
}
