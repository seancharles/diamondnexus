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
use ForeverCompanies\Salesforce\Model\ResourceModel\Map\Collection as MapCollectionFactory;
use Magento\Cms\Model\PageFactory;
use Magento\Backend\Model\View\Result\ForwardFactory;

/**
 * Class NewAction: Create new a mapping
 *
 * @package ForeverCompanies\Salesforce\Controller\Adminhtml\Map
 */
class NewAction extends MapController
{
    /**
     * @var \Magento\Backend\Model\View\Result\Forward
     */
    protected $resultForwardFactory;

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
        MapFactory  $mapFactory,
        PageFactory $resultPageFactory,
        ForwardFactory $resultForwardFactory,
        MapCollectionFactory $collectionFactory
    ) {
        $this->resultForwardFactory = $resultForwardFactory;
        parent::__construct($context, $coreRegistry, $resultPageFactory, $mapFactory, $collectionFactory);
    }


    /**
     * Forward to edit controller
     *
     * @return \Magento\Backend\Model\View\Result\Forward
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Forward $resultForward  */
        $resultForward = $this->resultForwardFactory->create();
        return $resultForward->forward('edit');
    }
}
