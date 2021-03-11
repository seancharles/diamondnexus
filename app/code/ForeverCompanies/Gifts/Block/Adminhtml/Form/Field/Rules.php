<?php
namespace ForeverCompanies\Gifts\Block\Adminhtml\Form\Field;

use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Element\BlockInterface;

/**
 * Class Ranges
 */
class Rules extends AbstractFieldArray
{
    /**
     * @var AttributeSetIdColumn
     */
    private $attributeSetIdRenderer;

    /**
     * @var MetalTypeColumn
     */
    private $metalTypeRenderer;

    /**
     * Prepare rendering the new field by adding all the needed columns
     */
    protected function _prepareToRender()
    {
        $this->addColumn(
            'attribute_set_id',
            ['label' => __('Attribute Set ID'), 'class' => 'required-entry', 'renderer' => $this->getAttributeSetId()]
        );
        $this->addColumn(
            'metal_type',
            ['label' => __('Metal Type'), 'class' => 'required-entry', 'renderer' => $this->getMetalType()]
        );
        $this->addColumn('sku', ['label' => __('SKU'), 'class' => 'required-entry', 'required' => true]);
        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add');
    }

    /**
     * Prepare existing row data object
     *
     * @param DataObject $row
     * @throws LocalizedException
     */
    protected function _prepareArrayRow(DataObject $row): void
    {
        $options = [];

        $attributeSetId = $row->getData('attribute_set_id');
        if ($attributeSetId !== null) {
            $options['option_' . $this->getAttributeSetId()->calcOptionHash($attributeSetId)] = 'selected="selected"';
        }
        $metalType = $row->getData('metal_type');
        if ($metalType !== null) {
            $options['option_' . $this->getMetalType()->calcOptionHash($metalType)] = 'selected="selected"';
        }

        $row->setData('option_extra_attrs', $options);
    }

    /**
     * @return AttributeSetIdColumn
     * @throws LocalizedException
     */
    private function getAttributeSetId()
    {
        if (!$this->attributeSetIdRenderer) {
            $this->attributeSetIdRenderer = $this->getLayout()->createBlock(
                AttributeSetIdColumn::class,
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
        }
        return $this->attributeSetIdRenderer;
    }

    /**
     * @return MetalTypeColumn|BlockInterface
     * @throws LocalizedException
     */
    private function getMetalType()
    {
        if (!$this->metalTypeRenderer) {
            $this->metalTypeRenderer = $this->getLayout()->createBlock(
                MetalTypeColumn::class,
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
        }
        return $this->metalTypeRenderer;
    }
}
