<?php

namespace ForeverCompanies\CustomAttributes\Block\System\Config;

use ForeverCompanies\CustomAttributes\Helper\TransformData;
use Magento\Backend\Block\Template\Context;
use Magento\Catalog\Model\ProductRepository;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\File\Csv;
use Magento\Framework\Message\ManagerInterface;
use Magento\Store\Model\StoreManagerInterface;

class DeleteByCsv extends Field
{
    /**
     * @var string
     */
    protected $_template = 'ForeverCompanies_CustomAttributes::system/config/deletebycsv.phtml';

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
        return $this->getUrl('forevercompanies_customattributes/system_config/deletebycsv');
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
                'id' => 'deletebycsv_button',
                'label' => __('Delete products from file'),
            ]
        );

        return $button->toHtml();
    }
}
