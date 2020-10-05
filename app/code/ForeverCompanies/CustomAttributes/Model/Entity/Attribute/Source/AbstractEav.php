<?php

declare(strict_types=1);

namespace ForeverCompanies\CustomAttributes\Model\Entity\Attribute\Source;

use Magento\Catalog\Model\Product\Option;
use Magento\Catalog\Model\Product\Option\Value;
use Magento\Eav\Model\Entity\Attribute\Source\SpecificSourceInterface;
use Magento\Eav\Model\Entity\Attribute\Source\Table;
use Magento\Framework\Api\CustomAttributesDataInterface;

abstract class AbstractEav extends Table implements SpecificSourceInterface
{
    protected $eq = '';

    /**
     * @param CustomAttributesDataInterface $entity
     * @return array
     */
    public function getOptionsFor(CustomAttributesDataInterface $entity): array
    {
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

    protected function unsetOptions(array $values, array $options, string $eq)
    {
        /** @var Value $value */
        foreach ($values as $value) {
            $options = $this->checkOptionAndValue($value->getTitle(), $options, $eq);
        }
        return $options;
    }

    private function checkOptionAndValue(string $title, array $options, string $eq)
    {
        foreach ($options as $key => $option) {
            switch ($eq) {
                case 'float':
                    if ((float)$title == (float)$option['label']) {
                        unset($options[$key]);
                        return $options;
                    }
                    break;
                case 'int':
                    if ((int)$title == (int)$option['label']) {
                        unset($options[$key]);
                        return $options;
                    }
                    break;
                case 'string':
                    if ((string)$title == (string)$option['label']) {
                        unset($options[$key]);
                        return $options;
                    }
                    break;
            }
        }
        return $options;
    }
}