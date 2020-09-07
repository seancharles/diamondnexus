<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace ForeverCompanies\CustomAttributes\Helper;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Product\Gallery;
use Magento\Eav\Model\Config;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\ResourceConnection;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

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

    /**
     * @var Config
     */
    protected $eavConfig;

    /**
     * Media constructor.
     * @param Context $context
     * @param ResourceConnection $resourceConnection
     * @param ProductRepositoryInterface $productRepository
     * @param Config $eavConfig
     */
    public function __construct(
        Context $context,
        ResourceConnection $resourceConnection,
        ProductRepositoryInterface $productRepository,
        Config $eavConfig
    ) {
        parent::__construct($context);
        $this->resourceConnection = $resourceConnection;
        $this->productRepository = $productRepository;
        $this->eavConfig = $eavConfig;
    }

    /**
     * @param array $images
     * @return array
     */
    public function addFieldsToMedia(array $images)
    {
        $connection = $this->resourceConnection->getConnection();
        $mediaGallery = $connection->getTableName(Gallery::GALLERY_VALUE_TABLE);

        foreach ($images as &$image) {
            $select = $connection->select();
            $select->from($mediaGallery)->where('value_id = ?', $image['value_id']);
            $row = $connection->fetchRow($select);
            $image['catalog_product_option_type_id'] = $row['catalog_product_option_type_id'];
            $image['catalog_product_bundle_selection_id'] = $row['catalog_product_bundle_selection_id'];
            $image['tags'] = $row['tags'];
        }

        return $images;
    }

    /**
     * @param $mediaImages
     */
    public function saveFieldsToMedia($mediaImages)
    {
        $connection = $this->resourceConnection->getConnection();
        $mediaGallery = $connection->getTableName(Gallery::GALLERY_VALUE_TABLE);
        foreach ($mediaImages as $image) {
            $id = $image['value_id'] ?? $image['id'];
            $optionType = $image['catalog_product_option_type_id'] ?? 0;
            $selectionId = $image['catalog_product_bundle_selection_id'] ?? 0;
            $tags = $image['tags'] ?? '';
            $connection->update(
                $mediaGallery,
                [
                    'catalog_product_option_type_id' => $optionType,
                    'catalog_product_bundle_selection_id' => $selectionId,
                    'tags' => $tags
                ],
                ['value_id = ?' => $id]
            );
        }
    }

    /**
     * @param array $links
     * @return array
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function prepareBundleSelectionsFromLinks(array $links)
    {
        $data = [];
        $metalTypeSource = $this->eavConfig->getAttribute(Product::ENTITY, 'gemstone')->getSource();
        /** @var \Magento\Bundle\Model\Link $link */
        foreach ($links as $link) {
            $product = $this->productRepository->get($link->getSku());
            $data[] = [
                'id' => $link->getId(),
                'label' => $metalTypeSource->getOptionText($product->getData('gemstone'))
            ];
        }
        return $data;
    }
}
