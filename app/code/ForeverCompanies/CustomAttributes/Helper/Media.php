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

    const CUSTOM_UI_ROLES = [
        'Default',
        'Hover',
        'Base',
        'Small',
        'Swatch',
        'Thumbnail'
    ];

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
        foreach ($images as &$image) {
            $select = $this->getGalleryValues($connection, $image['value_id']);
            $row = $connection->fetchRow($select);
            $image['catalog_product_option_type_id'] = $row['catalog_product_option_type_id'];
            $image['catalog_product_bundle_selection_id'] = $row['catalog_product_bundle_selection_id'];
            $image['tags'] = $row['tags'];
            $image['ui_role'] = $row['ui_role'];
            $image['matching_band_product'] = $row['matching_band_product'];
            $image['metal_type'] = $row['metal_type'];
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
            $uiRole = $image['ui_role'] ?? '';
            $linkedProduct = $image['matching_band_product'] ?? '';
            $metalType = $image['metal_type'] ?? '';
            $connection->update(
                $mediaGallery,
                [
                    'catalog_product_option_type_id' => $optionType,
                    'catalog_product_bundle_selection_id' => $selectionId,
                    'tags' => $tags,
                    'ui_role' => $uiRole,
                    'matching_band_product' => $linkedProduct,
                    'metal_type' => $metalType
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

    /**
     * @param $valueId
     * @return array
     */
    public function getCustomMediaOptions($valueId)
    {
        $connection = $this->resourceConnection->getConnection();
        $select = $this->getGalleryValues(
            $connection,
            $valueId
        )->joinLeft(
            ['type_title' => 'catalog_product_option_type_title'],
            'gallery_value.catalog_product_option_type_id = type_title.option_type_id',
            ['type_title_value' => 'type_title.title']
        )->joinLeft(
            ['type_value' => 'catalog_product_option_type_value'],
            'gallery_value.catalog_product_option_type_id = type_value.option_type_id',
            ['option_id']
        )->joinLeft(
            ['option_title' => 'catalog_product_option_title'],
            'type_value.option_id = option_title.option_id',
            ['option_title_value' => 'option_title.title']
        );
        return $connection->fetchRow($select);
    }

    /**
     * @param $connection
     * @param $valueId
     * @return mixed
     */
    protected function getGalleryValues($connection, $valueId)
    {
        return $connection->select()->from(
            ['gallery_value' => Gallery::GALLERY_VALUE_TABLE]
        )->where('gallery_value.value_id = ?', $valueId);
    }

    /**
     * @param $valueId
     * @return mixed
     */
    public function getImagePath($valueId)
    {
        $connection = $this->resourceConnection->getConnection();
        $select = $connection->select()->from(
            ['gallery' => Gallery::GALLERY_TABLE]
        )->where('gallery.value_id = ?', $valueId);
        return $connection->fetchRow($select);
    }
}
