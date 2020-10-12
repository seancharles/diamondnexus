<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace ForeverCompanies\Salesforce\Controller\Adminhtml;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Cms\Model\PageFactory;
use Magento\Framework\Registry;
use ForeverCompanies\Salesforce\Model\MapFactory;
use ForeverCompanies\Salesforce\Model\ResourceModel\Map\Collection;

/**
 * Reviews admin controller
 * @package ForeverCompanies\Salesforce\Controller\Adminhtml
 */
abstract class Map extends Action
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry = null;

    /**
     * Map model factory
     *
     * @var MapFactory
     */
    protected $mapFactory;

    /**
     * Map Collection factory
     *
     * @var   Collection
     */
    protected $collectionFactory;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * Page factory
     *
     * @var PageFactory
     */
    protected $resultPage;

    /**
     * @param Context              $context
     * @param Registry             $coreRegistry
     * @param PageFactory          $resultPageFactory
     * @param MapFactory           $mapFactory
     * @param Collection           $collectionFactory
     */
    public function __construct(
        Context $context,
        Registry $coreRegistry,
        PageFactory $resultPageFactory,
        MapFactory  $mapFactory,
        Collection $collectionFactory
    ) {
        $this->_context           = $context;
        $this->coreRegistry       = $coreRegistry;
        $this->mapFactory        = $mapFactory;
        $this->collectionFactory = $collectionFactory;
        $this->resultPageFactory  = $resultPageFactory;
        parent::__construct($context);
    }

    /**
     * Init actions
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    protected function _initAction()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('ForeverCompanies_Salesforce::mapping')
                   ->addBreadcrumb(__('Manage Mapping'), -_('Manage Mapping'));
        return $resultPage;
    }

    /**
     * Check ACL
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('ForeverCompanies_Salesforce::mapping');
    }
}
