<?php

declare(strict_types=1);

namespace ForeverCompanies\CustomAttributes\Model\Entity\Attribute\Source;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Option;
use Magento\Catalog\Model\Product\Option\Value;
use Magento\Framework\Api\CustomAttributesDataInterface;

class Shape extends AbstractEav
{
    protected $eq = 'string';

    /**
     * @param CustomAttributesDataInterface $entity
     * @return array
     */
    public function getOptionsFor(CustomAttributesDataInterface $entity): array
    {
        /** @var Product $entity */
        $options = $this->getAllOptions(true, true);
        /** @var Option $option */
        $entityOptions = $entity->getOptions();
        foreach ($entityOptions as $option) {
            if ($option->getData('customization_type') == $this->getAttribute()->getAttributeCode()) {
                $options = $this->unsetOptions($option->getValues(), $options, $this->eq);
                return $options;
            }
        }
        return $options;
    }

    /**
     * @param array $values
     * @param array $options
     * @param string $eq
     * @return array
     */
    protected function unsetOptions(array $values, array $options, string $eq)
    {
        /** @var Value $value */
        foreach ($values as $value) {
            $options = $this->checkOptionAndValue($value->getTitle(), $options, $eq);
        }
        return $options;
    }

    /**
     * @param string $title
     * @param array $options
     * @param string $eq
     * @return array
     */
    private function checkOptionAndValue(string $title, array $options, string $eq)
    {
        foreach ($options as $key => $option) {
            if ($title == 'Round Brilliant') {
                $title = 'Round';
            }
            if ((string)$title == (string)$option['label']) {
                unset($options[$key]);
                return $options;
            }
        }
        return $options;
    }
}
