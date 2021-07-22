<?php
namespace ForeverCompanies\Forms\Model;

use Magento\Framework\Model\AbstractModel;

class Submission extends AbstractModel
{
    protected $_eventPrefix = 'forms_submission';
    
    public function _construct()
    {
        $this->_init("ForeverCompanies\Forms\Model\ResourceModel\Submission");
    }
}
