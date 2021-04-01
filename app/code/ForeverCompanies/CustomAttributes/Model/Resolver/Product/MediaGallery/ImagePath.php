<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace ForeverCompanies\CustomAttributes\Model\Resolver\Product\MediaGallery;

use ForeverCompanies\CustomAttributes\Helper\Media;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

/**
 * Returns media url
 */
class ImagePath implements ResolverInterface
{
    /**
     * @var Media
     */
    protected $mediaHelper;

    /**
     * CustomOptions constructor.
     * @param Media $mediaHelper
     */
    public function __construct(
        Media $mediaHelper
    ) {
        $this->mediaHelper = $mediaHelper;
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
        if (!array_key_exists('id', $value)) {
            return false;
        }
        $data = $this->mediaHelper->getImagePath($value['id']);
        return array_key_exists('value', $data) ? $data['value'] : '';
    }
}
