<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace ForeverCompanies\CustomAttributes\Helper;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Bundle\Api\Data\LinkInterfaceFactory;
use Magento\Bundle\Api\Data\LinkInterface;

class Link extends AbstractHelper
{
    /**
     * @var LinkInterfaceFactory
     */
    protected $linkFactory;

    /**
     * @var array
     */
    protected $usedProducts = [];

    /**
     * Link constructor.
     * @param Context $context
     * @param LinkInterfaceFactory $linkFactory
     */
    public function __construct(
        Context $context,
        LinkInterfaceFactory $linkFactory
    )
    {
        parent::__construct($context);
        $this->linkFactory = $linkFactory;
    }

    /**
     * @param ProductInterface $product
     * @param float $itemPrice
     * @return LinkInterface
     */
    public function createNewLink(ProductInterface $product, float $itemPrice)
    {
        $link = $this->linkFactory->create();
        $link->setSku($product->getSku());
        $link->setData('name', $product->getName());
        $link->setData('selection_qty', 1);
        $link->setData('qty', 1);
        $link->setData('can_change_qty', 1);
        $link->setData('product_id', $product->getId());
        $link->setData('record_id', $product->getId());
        $link->setIsDefault(false);
        $link->setData('selection_price_value', $itemPrice);
        $link->setData('price', (string)$itemPrice);
        $link->setData('selection_price_type', LinkInterface::PRICE_TYPE_FIXED);
        $link->setData('price_type', LinkInterface::PRICE_TYPE_FIXED);
        return $link;
    }
}
