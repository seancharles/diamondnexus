<?php
declare(strict_types=1);

namespace ForeverCompanies\Gifts\Block\Adminhtml\Form\Field;

use Magento\Catalog\Model\Product;
use Magento\Eav\Model\Config;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Element\Context;
use Magento\Framework\View\Element\Html\Select;

class MetalTypeColumn extends Select
{
    /**
     * @var Config
     */
    protected $eavConfig;

    /**
     * AttributeSetIdColumn constructor.
     * @param Context $context
     * @param Config $eavConfig
     * @param array $data
     */
    public function __construct(Context $context, Config $eavConfig, array $data = [])
    {
        parent::__construct($context, $data);
        $this->eavConfig = $eavConfig;
    }

    /**
     * Set "name" for <select> element
     *
     * @param string $value
     * @return $this
     */
    public function setInputName($value)
    {
        return $this->setData('name', $value);
    }

    /**
     * Set "id" for <select> element
     *
     * @param $value
     * @return $this
     */
    public function setInputId($value)
    {
        return $this->setId($value);
    }

    /**
     * Render block HTML
     *
     * @return string
     * @throws LocalizedException
     */
    public function _toHtml(): string
    {
        if (!$this->getOptions()) {
            $this->setOptions($this->getSourceOptions());
        }
        return parent::_toHtml();
    }

    /**
     * @return array
     * @throws LocalizedException
     */
    private function getSourceOptions(): array
    {
        $attributeSource = $this->eavConfig->getAttribute(Product::ENTITY, 'metal_type')->getSource();
        return $attributeSource->getAllOptions();
    }
}
