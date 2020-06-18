<?php

namespace ForeverCompanies\DynamicBundle\Block\Adminhtml\Product\Edit\Tabs;

class Plugin
{
	protected $ignoredTabs = [];
	protected $updatedTabs = [];
	
	public function __construct($ignoredTabs = [], $updatedTabs = [])
	{
		$this->ignoredTabs = $ignoredTabs;
		$this->updatedTabs = $updatedTabs;
	}
	
	public function aroundAddTab(
		\Magento\Catalog\Block\Adminhtml\Product\Edit\Tabs $subject,
		\Closure $proceed,
		$tabId,
		$tab
	) {
		if ($subject->getProduct()->getTypeId() == \ForeverCompanies\DynamicBundle\Model\Product\Type::TYPE_ID)
		{
			if (in_array($tabId, $this->ignoredTabs) {
				return $subject;
			}
			
			if (isset($this->updateTabs[$tabId]) && is_array($tab) == true) {
				$tab = array_merge($tab, $this->updatedTabs[$tabId]);
			}
		}
		
		return $proceed($tabId, $tab);
	}
}