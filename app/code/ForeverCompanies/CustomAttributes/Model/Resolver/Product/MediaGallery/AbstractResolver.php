<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace ForeverCompanies\CustomAttributes\Model\Resolver\Product\MediaGallery;

use ForeverCompanies\CustomAttributes\Helper\Media;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

/**
 * Returns media url
 */
abstract class AbstractResolver
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
     * @param Field $field
     * @param $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @return array
     * @throws LocalizedException
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        if (!isset($value['image_type']) && !isset($value['file'])) {
            throw new LocalizedException(__('"image_type" value should be specified'));
        }

        if (!isset($value['model'])) {
            throw new LocalizedException(__('"model" value should be specified'));
        }

        // this was added to prevent a graphql error when querying for the following attributes:
        // * image
        // * small_image
        // * thumbnail
        // @todo fix this so the custom attributes will be returned when querying the above
        if (!array_key_exists('id', $value)) {
            return false;
        }

        return $this->mediaHelper->getCustomMediaOptions($value['id']);
    }
}
