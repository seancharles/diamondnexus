<?php
declare(strict_types=1);

namespace ForeverCompanies\Gifts\Block\Adminhtml\Form\Field;

use Magento\Catalog\Model\Product;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory;
use Magento\Framework\View\Element\Context;
use Magento\Framework\View\Element\Html\Select;

class AttributeSetIdColumn extends Select
{
    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * AttributeSetIdColumn constructor.
     * @param Context  $context
     * @param CollectionFactory $collectionFactory
     * @param array $data
     */
    public function __construct(Context  $context, CollectionFactory $collectionFactory, array $data = [])
    {
        parent::__construct($context, $data);
        $this->collectionFactory = $collectionFactory;
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
     */
    private function getSourceOptions(): array
    {
        $collection = $this->collectionFactory->create();
        $result = [];
        foreach ($collection->getItems() as $item) {
            $result[] = [
                'label' => $item['attribute_set_name'],
                'value' => $item['attribute_set_id']
            ];
        }
        return $result;
    }
}
