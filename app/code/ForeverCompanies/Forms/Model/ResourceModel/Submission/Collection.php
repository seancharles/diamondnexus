<?php 
namespace ForeverCompanies\Forms\Model\ResourceModel\Submission;
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection{
	public function _construct(){
		$this->_init(
			"ForeverCompanies\Forms\Model\Submission",	"ForeverCompanies\Forms\Model\ResourceModel\Submission"
		);
	}
}
 ?>