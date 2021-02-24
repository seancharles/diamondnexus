<?php

namespace ForeverCompanies\StonesIntermediary\Block;

class AddSupplier extends \Magento\Backend\Block\Template
{
    /**
     * Get form action URL for POST booking request
     *
     * @return string
     */
    public function getFormAction()
    {
        return $this->getUrl('diamond/supplier/add', ['_secure' => true]);
    }
}
