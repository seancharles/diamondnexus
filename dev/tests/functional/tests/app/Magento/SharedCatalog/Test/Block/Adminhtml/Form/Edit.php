<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SharedCatalog\Test\Block\Adminhtml\Form;

use Magento\Backend\Test\Block\Widget\FormTabs;
use Magento\Mtf\Client\Element\SimpleElement;
use Magento\SharedCatalog\Model\SharedCatalog;
use Magento\Mtf\Client\Locator;

/**
 * Form for creation of the shared catalog.
 */
class Edit extends FormTabs
{
    /**
     * Name of default tab.
     *
     * @var string
     */
    private $defaultTabName = 'catalog_details';

    /**
     * Css selector shared catalog name.
     *
     * @var string
     */
    private $sharedCatalogName = '[name="catalog_details[name]"]';

    /**
     * Css selector shared catalog type.
     *
     * @var string
     */
    private $sharedCatalogType = '[name="catalog_details[type]"]';

    /**
     * Css selector tax class.
     *
     * @var string
     */
    private $taxClass = '[name="catalog_details[tax_class_id]"]';

    /**
     * Css selector tax class selected option.
     *
     * @var string
     */
    private $taxClassSelectedOption = '[name="catalog_details[tax_class_id]"] option[value="%s"]';

    /**
     * Fill specified form data.
     *
     * @param array $fields
     * @param SimpleElement $element
     * @return void
     */
    protected function _fill(array $fields, SimpleElement $element = null)
    {
        $fields['type']['value'] = $fields['type']['value']
            ? SharedCatalog::CATALOG_PUBLIC
            : SharedCatalog::CATALOG_CUSTOM;
        parent::_fill($fields, $element);
    }

    /**
     * Set shared catalog name.
     *
     * @param string $name
     * @return void
     */
    public function setName($name)
    {
        $this->_rootElement->find($this->sharedCatalogName)->setValue($name);
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->_rootElement->find($this->sharedCatalogType)->getValue();
    }

    /**
     * Fill type value.
     *
     * @param string $value
     * @return void
     */
    public function setType($value)
    {
        $this->_rootElement->find($this->sharedCatalogType, Locator::SELECTOR_CSS, 'select')->setValue($value);
    }

    /**
     * Return tab selector by name.
     *
     * @param string $tabName
     * @return string
     */
    public function getTabSelector($tabName = '')
    {
        if ($tabName === '') {
            $tabName = $this->defaultTabName;
        }
        return $this->containers[$tabName]['selector'];
    }

    /**
     * Get shared catalog customer tax class.
     *
     * @return string
     */
    public function getCustomerTaxClass()
    {
        $taxClassValue = $this->_rootElement->find($this->taxClass)->getValue();
        return trim($this->_rootElement->find(sprintf($this->taxClassSelectedOption, $taxClassValue))->getText());
    }

    /**
     * Set shared catalog customer tax class.
     *
     * @param string $taxClassName
     * @return void
     */
    public function setCustomerTaxClass($taxClassName)
    {
        $this->_rootElement->find($this->taxClass, Locator::SELECTOR_CSS, 'select')->setValue($taxClassName);
    }
}
