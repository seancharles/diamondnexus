<?php

declare(strict_types=1);

namespace ForeverCompanies\CustomAttributes\Model\Resolver\Product\MediaGallery;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

/**
 * Returns media url
 */
class ImageUrl implements ResolverInterface
{
    private string $url = 'https://res.cloudinary.com/foco/image/upload/media/catalog/product';

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
        return $this->url;
    }
}
