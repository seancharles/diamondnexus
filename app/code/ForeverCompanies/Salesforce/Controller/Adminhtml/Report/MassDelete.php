<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);


namespace ForeverCompanies\Salesforce\Controller\Adminhtml\Report;

use ForeverCompanies\Salesforce\Controller\Adminhtml\Report as  ReportController;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Page;
use Magento\Framework\Registry;
use Magento\Framework\View\LayoutFactory;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Framework\View\Result\PageFactory;
use Magento\Backend\Model\View\Result\ForwardFactory;
use Magento\Framework\Controller\ResultFactory;
use ForeverCompanies\Salesforce\Model\ReportFactory as ReportFactory;
use ForeverCompanies\Salesforce\Model\ResourceModel\Report\CollectionFactory as ReportCollectionFactory;

/**
 * Class MassDelete
 *
 * @package ForeverCompanies\Salesforce\Controller\Adminhtml\Report
 */
class MassDelete extends ReportController
{
    /**
     * Mass Action Filter
     *
     * @var Filter
     */
    protected $filter;

    /**
     * @param Context                 $context
     * @param ReportFactory           $reportFactory
     * @param LayoutFactory           $layoutFactory
     * @param PageFactory             $resultPageFactory
     * @param Filter                  $filter
     * @param ForwardFactory          $resultForwardFactory
     * @param ReportCollectionFactory $collectionFactory
     */
    public function __construct(
        Context $context,
        ReportFactory $reportFactory,
        LayoutFactory $layoutFactory,
        PageFactory $resultPageFactory,
        Filter $filter,
        ForwardFactory $resultForwardFactory,
        ReportCollectionFactory $collectionFactory
    ) {
        $this->filter            = $filter;
        $this->collectionFactory = $collectionFactory;
        parent::__construct($context, $reportFactory,
            $layoutFactory, $resultPageFactory, $resultForwardFactory);
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
