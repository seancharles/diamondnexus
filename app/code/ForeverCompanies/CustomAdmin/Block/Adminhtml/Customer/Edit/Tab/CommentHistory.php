<?php
namespace ForeverCompanies\CustomAdmin\Block\Adminhtml\Customer\Edit\Tab;

use Magento\Backend\Block\Template\Context;
use Magento\Customer\Controller\RegistryConstants;
use Magento\Framework\Phrase;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template;
use Magento\Ui\Component\Layout\Tabs\TabInterface;

class CommentHistory extends Template implements TabInterface
{
    /**
     * Core registry
     *
     * @var Registry
     */
    protected $coreRegistry;
    /**
     * @param Context $context
     * @param Registry $registry
     * @param array $data
     */

    public function __construct(
        Context $context,
        Registry $registry,
        array $data = []
    ) {
        $this->coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * @return string|null
     */
    public function getCustomerId()
    {
        return $this->coreRegistry->registry(RegistryConstants::CURRENT_CUSTOMER_ID);
    }
    /**
     * @return Phrase
     */
    public function getTabLabel()
    {
        return __('Comment History');
    }
    /**
     * @return Phrase
     */
    public function getTabTitle()
    {
        return __('Comment History');
    }
    /**
     * @return bool
     */
    public function canShowTab()
    {
        if ($this->getCustomerId()) {
            return true;
        }
        return false;
    }

    /**
     * @return bool
     */
    public function isHidden()
    {
        if ($this->getCustomerId()) {
            return false;
        }
        return true;
    }
    /**
     * Tab class getter
     *
     * @return string
     */
    public function getTabClass()
    {
        return '';
    }
    /**
     * Return URL link to Tab content
     *
     * @return string
     */
    public function getTabUrl()
    {
        //replace the tab with the url you want
        return $this->getUrl('customadmin/*/comment', ['_current' => true]);
    }
    /**
     * Tab should be loaded trough Ajax call
     *
     * @return bool
     */
    public function isAjaxLoaded()
    {
        return true;
    }
}
