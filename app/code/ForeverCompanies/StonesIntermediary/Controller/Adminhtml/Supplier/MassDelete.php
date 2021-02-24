<?php

namespace ForeverCompanies\StonesIntermediary\Controller\Adminhtml\Supplier;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Redirect;
use ForeverCompanies\StonesIntermediary\Model\ResourceModel\StonesSupplier\CollectionFactory;
use ForeverCompanies\StonesIntermediary\Api\StonesSupplierManagementInterface;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\NotFoundException;
use Magento\Ui\Component\MassAction\Filter;

class MassDelete extends Action implements HttpPostActionInterface
{
    /**
     * Authorization level
     */
    const ADMIN_RESOURCE = 'ForeverCompanies_StonesIntermediary::stones_supplier';

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var StonesSupplierManagementInterface
     */
    private $supplierManagement;

    /**
     * @var Filter
     */
    protected $filter;

    /**
     * Constructor
     *
     * @param Context $context
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     * @param StonesSupplierManagementInterface $supplierManagement
     */
    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory,
        StonesSupplierManagementInterface $supplierManagement
    ) {
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        $this->supplierManagement = $supplierManagement;
        parent::__construct($context);
    }

    /**
     * Category delete action
     *
     * @return Redirect
     */
    public function execute(): Redirect
    {
        if (!$this->getRequest()->isPost()) {
            throw new NotFoundException(__('Page not found'));
        }
        $collection = $this->filter->getCollection($this->collectionFactory->create());
        $idDeleted = 0;
        foreach ($collection->getItems() as $supplier) {
            $this->supplierManagement->deleteStonesSupplier($supplier->getData('id'));
            $idDeleted++;
        }

        if ($idDeleted) {
            $this->messageManager->addSuccessMessage(
                __('A total of %1 record(s) have been deleted.', $idDeleted)
            );
        }
        return $this->resultFactory->create(ResultFactory::TYPE_REDIRECT)
            ->setPath('diamond_importer/stones_supplier/index');
    }
}
