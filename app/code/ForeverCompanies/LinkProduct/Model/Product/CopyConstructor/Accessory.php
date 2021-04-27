<?php
/**
 * Copyright © ForeverCompanies LLC. All rights reserved.
 * See COPYING.txt for license details.
 * http://www.forevercompanies.com | support@forevercompanies.com
 */

namespace ForeverCompanies\LinkProduct\Model\Product\CopyConstructor;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\CopyConstructorInterface;
use Magento\Catalog\Model\Product\Link;

class Accessory implements CopyConstructorInterface
{
    /**
     * Build product links
     *
     * @param Product $product
     * @param Product $duplicate
     * @return void
     */
    public function build(Product $product, Product $duplicate)
    {
        $data = [];
        $attributes = [];

        $link = $product->getLinkInstance();
        $link->useAccessoryLinks();
        foreach ($link->getAttributes() as $attribute) {
            if (isset($attribute['code'])) {
                $attributes[] = $attribute['code'];
            }
        }
        /** @var Link $link  */
        foreach ($product->getAccessoryLinkCollection() as $link) {
            $data[$link->getLinkedProductId()] = $link->toArray($attributes);
        }

        $duplicate->setAccessoryLinkData($data);
    }
}
