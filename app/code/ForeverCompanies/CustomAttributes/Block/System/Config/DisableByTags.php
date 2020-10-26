<?php

namespace ForeverCompanies\CustomAttributes\Block\System\Config;

use ForeverCompanies\CustomAttributes\Helper\TransformData;
use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Exception\LocalizedException;

class DisableByTags extends Field
{
    /**
     * @var string
     */
    protected $_template = 'ForeverCompanies_CustomAttributes::system/config/disablebytags.phtml';

    /**
     * @var TransformData
     */
    protected $helper;

    /**
     * @param Context $context
     * @param TransformData $helper
     * @param array $data
     */
    public function __construct(
        Context $context,
        TransformData $helper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->helper = $helper;
    }

    /**
     * Remove scope label
     *
     * @param AbstractElement $element
     * @return string
     */
    public function render(AbstractElement $element)
    {
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();
        return parent::render($element);
    }

    /**
     * Return element html
     *
     * @param AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        return $this->_toHtml();
    }

    /**
     * Return ajax url for collect button
     *
     * @return string
     */
    public function getAjaxUrl()
    {
        return $this->getUrl('forevercompanies_customattributes/system_config/disablebytags');
    }

    public function getCount()
    {
        return $this->helper->getProductsForDisableCollection()->count();
    }

    /**
     * Generate collect button html
     *
     * @return string
     * @throws LocalizedException
     */
    public function getButtonHtml()
    {
        $button = $this->getLayout()->createBlock(
            \Magento\Backend\Block\Widget\Button::class
        )->setData(
            [
                'id' => 'disablebytags_button',
                'label' => __('Disable products (' . $this->getCount() . ')'),
            ]
        );

        return $button->toHtml();
    }
}
