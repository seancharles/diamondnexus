<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace ForeverCompanies\LinkProduct\Model\Resolver\Batch;

use ForeverCompanies\LinkProduct\Model\Product\Link;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\Resolver\BatchResponse;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;

/**
 * CrossSell Products Resolver
 */
class LinkProducts extends \Magento\RelatedProductGraphQl\Model\Resolver\Batch\AbstractLikedProducts
{
    /**
     * @inheritDoc
     */
    protected function getNode(): string
    {
        return 'linkproduct_products';
    }

    /**
     * @inheritDoc
     */
    protected function getLinkType(): int
    {
        return Link::LINK_TYPE_ACCESSORY;
    }
}