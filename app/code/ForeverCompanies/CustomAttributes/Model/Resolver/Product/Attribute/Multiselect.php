<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace ForeverCompanies\CustomAttributes\Model\Resolver\Product\Attribute;

use Magento\Catalog\Model\Product;
use Magento\Eav\Model\Config;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

/**
 * Returns media url
 */
class Multiselect implements ResolverInterface
{
    /**
     * @var Config
     */
    protected $eavConfig;

    /**
     * CustomOptions constructor.
     * @param Config $eavConfig
     */
    public function __construct(
        Config $eavConfig
    ) {
        $this->eavConfig = $eavConfig;
    }

    /**
     * @inheritdoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        if (!isset($value['model'])) {
            throw new LocalizedException(__('"model" value should be specified'));
        }
        $source = $this->eavConfig->getAttribute(Product::ENTITY, $field->getName())->getSource();
        if (!isset($value[$field->getName()])) {
            return [''];
        }
        $values = explode(',', $value[$field->getName()]);
        $result = [];
        foreach ($values as $val) {
            $result[] = $val . ', ' . $source->getOptionText($val);
        }
        return $result;
    }
}
