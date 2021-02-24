<?php

namespace ForeverCompanies\StonesIntermediary\Block;

use ForeverCompanies\StonesIntermediary\Model\StonesSupplier;
use ForeverCompanies\StonesIntermediary\Model\StonesSupplierManagement;
use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;

class ViewSupplier extends Template
{
    /**
     * @var StonesSupplier
     */
    protected $supplier;

    /**
     * Construct
     *
     * @param Context $context
     * @param StonesSupplierManagement $supplierManagement
     * @param array $data
     */
    public function __construct(
        Context $context,
        StonesSupplierManagement $supplierManagement,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->supplier = $supplierManagement->getById($this->getId());
    }

    /**
     * Get form action URL for POST booking request
     *
     * @return string
     */
    public function getFormAction()
    {
        return $this->getUrl('diamond/supplier/view', ['_secure' => true]);
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->_request->getParam('id');
    }

    /**
     * @param string $data
     * @return array|mixed|null
     */
    public function getSupplier($data)
    {
        return $this->supplier->getData($data);
    }
}
