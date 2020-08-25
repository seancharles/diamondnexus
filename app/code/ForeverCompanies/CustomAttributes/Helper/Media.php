<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace ForeverCompanies\CustomAttributes\Helper;

use Magento\Catalog\Model\ResourceModel\Product\Gallery;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\ResourceConnection;
use Magento\Catalog\Api\ProductRepositoryInterface;

class Media extends AbstractHelper
{
    /**
     * @var ResourceConnection
     */
    protected $resourceConnection;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    public function __construct(
        Context $context,
        ResourceConnection $resourceConnection,
        ProductRepositoryInterface $productRepository
    ) {
        parent::__construct($context);
        $this->resourceConnection = $resourceConnection;
        $this->productRepository = $productRepository;
    }

    /**
     * @param array $images
     * @return array
     */
    public function addFieldsToMedia(array $images)
    {
        $connection = $this->resourceConnection->getConnection();
        $mediaGallery = $connection->getTableName(Gallery::GALLERY_VALUE_TABLE);

        foreach ($images as &$image)
        {
            $select = $connection->select();
            $select->from($mediaGallery)->where( 'value_id = ?', $image['value_id']);
            $row = $connection->fetchRow($select);
            $image['option_type_id'] = $row['catalog_product_option_type_id'];
            $image['bundle_selection_id'] = $row['catalog_product_bundle_selection_id'];
            $image['tags'] = $row['tags'];
        }

        return $images;
    }

    public function prepareBundleSelectionsFromLinks(array $links)
    {
        $data = [];
        /** @var \Magento\Bundle\Model\Link $link */
        foreach ($links as $link) {
            $product = $this->productRepository->get($link->getSku());
            /** TODO: get selected item label from SKU */
        }
    }
}
