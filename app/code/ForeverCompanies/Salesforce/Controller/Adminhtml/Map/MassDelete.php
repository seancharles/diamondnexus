<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace ForeverCompanies\Salesforce\Controller\Adminhtml\Map;

use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;
use Magento\Framework\Controller\ResultFactory;
use Magento\Ui\Component\MassAction\Filter;
use ForeverCompanies\Salesforce\Controller\Adminhtml\Map as MapController;
use Magento\Cms\Model\PageFactory;
use ForeverCompanies\Salesforce\Model\MapFactory;
use ForeverCompanies\Salesforce\Model\ResourceModel\Map\Collection
    as MapCollectionFactory;


/**
 * Class MassDelete Controller
 *
 * @package ForeverCompanies\Salesforce\Controller\Adminhtml\Map
 */
class MassDelete extends MapController
{
    /**
     * Mass Action Filter
     *
     * @var Filter
     */
    protected $filter;

    /**
     * Collection Factory
     *
     * @var MapCollectionFactory
     */
    protected $collectionFactory;

    /**
     * @param Context              $context
     * @param Registry             $coreRegistry
     * @param PageFactory          $resultPageFactory
     * @param MapFactory           $mapFactory
     * @param MapCollectionFactory $collectionFactory
     * @param Filter               $filter
     */
    public function __construct(
        Context $context,
        Registry $coreRegistry,
        PageFactory $resultPageFactory,
        MapFactory $mapFactory,
        MapCollectionFactory $collectionFactory,
        Filter $filter
    ) {
        $this->filter            = $filter;
        $this->collectionFactory = $collectionFactory;
        parent::__construct($context, $coreRegistry, $resultPageFactory, $mapFactory, $collectionFactory);
    }

    /**
     * execute action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $collection = $this->filter->getCollection($this->collectionFactory->create());
        $delete = 0;
        foreach ($collection as $item){
            /** @var \ForeverCompanies\Salesforce\Model\Map $item */
            $item->delete();
            $delete++;
        }
        $this->messageManager->addSuccess(__('A total of %1 record(s) have been deleted.', $delete));
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('*/*/');
    }

}
