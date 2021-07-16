<?php
namespace ForeverCompanies\Forms\Model\ResourceModel;

class Submission extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    public function _construct()
    {
        $this->_init("fc_form_submission", "submission_id");
    }
}
