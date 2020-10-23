<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace ForeverCompanies\CustomAttributes\Model\Resolver\Product\MediaGallery;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

/**
 * Returns media url
 */
class CustomOptions extends AbstractResolver implements ResolverInterface
{

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
        $options = parent::resolve($field, $context, $info, $value, $args);
        if ($options['option_title_value'] !== '' && $options['type_title_value']) {
            return $options['option_title_value'] . '--' . $options['type_title_value'];
        }
        return '';
    }
}
