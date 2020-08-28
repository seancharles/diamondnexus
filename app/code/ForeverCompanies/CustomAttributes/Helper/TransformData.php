<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace ForeverCompanies\CustomAttributes\Helper;

use Exception;
use Magento\Bundle\Api\Data\LinkInterface;
use Magento\Bundle\Api\Data\LinkInterfaceFactory;

use Magento\Bundle\Model\Product\Type;
use Magento\Catalog\Api\AttributeSetRepositoryInterface;
use Magento\Catalog\Api\Data\ProductAttributeMediaGalleryEntryInterface;
use Magento\Catalog\Api\Data\ProductCustomOptionInterface;

use Magento\Catalog\Api\Data\ProductExtension;
use Magento\Catalog\Api\Data\ProductExtensionFactory;
use Magento\Catalog\Api\Data\TierPriceInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Gallery\GalleryManagement;
use Magento\Catalog\Model\Product\Option;
use Magento\Catalog\Model\ProductRepository;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\CatalogInventory\Model\Stock\Item;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Eav\Model\Config;
use Magento\Framework\Api\Data\ImageContentInterface;
use Magento\Framework\Api\Data\ImageContentInterfaceFactory;
use Magento\Framework\Api\Data\VideoContentInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\StateException;
use Magento\Framework\Filesystem\DriverInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\MediaStorage\Model\ResourceModel\File\Storage\File;
use Magento\ProductVideo\Model\Product\Attribute\Media\ExternalVideoEntryConverter;
use Magento\Store\Model\StoreManagerInterface;
use Zend_Db_Select;

class TransformData extends AbstractHelper
{
    /**
     * @var CollectionFactory
     */
    protected $productCollectionFactory;

    /**
     * @var Config
     */
    protected $eav;

    /**
     * @var ProductRepository
     */
    protected $productRepository;

    /**
     * @var AttributeSetRepositoryInterface
     */
    protected $attrSetRepository;

    /**
     * @var Mapping
     */
    protected $mapping;

    /**
     * @var ProductFunctional
     */
    protected $productFunctionalHelper;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var Converter
     */
    protected $converter;

    /**
     * @var ProductExtensionFactory
     */
    protected $productExtensionFactory;

    /**
     * @var ExternalVideoEntryConverter
     */
    private $externalVideoEntryConverter;

    /**
     * @var File
     */
    protected $file;

    /**
     * @var Curl
     */
    protected $curl;

    /**
     * @var ImageContentInterfaceFactory
     */
    protected $imageContentFactory;

    /**
     * @var GalleryManagement
     */
    protected $galleryManagement;

    protected $mimeTypes = [
        'png' => 'image/png',
        'jpe' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'jpg' => 'image/jpeg',
        'gif' => 'image/gif',
        'bmp' => 'image/bmp',
        'ico' => 'image/vnd.microsoft.icon',
        'tiff' => 'image/tiff',
        'tif' => 'image/tiff',
        'svg' => 'image/svg+xml',
        'svgz' => 'image/svg+xml',
    ];
    /**
     * @var SerializerInterface
     */
    protected $serializer;

    /**
     * @var DriverInterface
     */
    protected $fileDriver;

    /**
     * @param Context $context
     * @param Config $config
     * @param CollectionFactory $collectionFactory
     * @param ProductRepository $productRepository
     * @param AttributeSetRepositoryInterface $attributeSetRepository
     * @param ExternalVideoEntryConverter $videoEntryConverter
     * @param Mapping $mapping
     * @param ProductFunctional $productFunctionalHelper
     * @param StoreManagerInterface $storeManager
     * @param Converter $converter
     * @param ProductExtensionFactory $productExtensionFactory
     * @param File $file
     * @param Curl $curl
     * @param ImageContentInterfaceFactory $imageContent
     * @param GalleryManagement $galleryManagement
     * @param SerializerInterface $serializer
     * @param DriverInterface $fileDriver
     */
    public function __construct(
        Context $context,
        Config $config,
        CollectionFactory $collectionFactory,
        ProductRepository $productRepository,
        AttributeSetRepositoryInterface $attributeSetRepository,
        ExternalVideoEntryConverter $videoEntryConverter,
        Mapping $mapping,
        ProductFunctional $productFunctionalHelper,
        StoreManagerInterface $storeManager,
        Converter $converter,
        ProductExtensionFactory $productExtensionFactory,
        File $file,
        Curl $curl,
        ImageContentInterfaceFactory $imageContent,
        GalleryManagement $galleryManagement,
        SerializerInterface $serializer,
        DriverInterface $fileDriver
    ) {
        parent::__construct($context);
        $this->eav = $config;
        $this->productCollectionFactory = $collectionFactory;
        $this->productRepository = $productRepository;
        $this->attrSetRepository = $attributeSetRepository;
        $this->externalVideoEntryConverter = $videoEntryConverter;
        $this->mapping = $mapping;
        $this->productFunctionalHelper = $productFunctionalHelper;
        $this->storeManager = $storeManager;
        $this->converter = $converter;
        $this->productExtensionFactory = $productExtensionFactory;
        $this->file = $file;
        $this->curl = $curl;
        $this->imageContentFactory = $imageContent;
        $this->galleryManagement = $galleryManagement;
        $this->serializer = $serializer;
        $this->fileDriver = $fileDriver;
    }

    /**
     * @return Collection
     */
    public function getProductsForDeleteCollection()
    {
        $table = 'catalog_product_entity_varchar';
        $attr = 'dev_tag';
        $where = 'like "%Removed as part of%"';
        return $this->getProductCollection($table, $attr, $where);
    }

    /**
     * @return Collection
     */
    public function getProductsForTransformCollection()
    {
        $table = 'catalog_product_entity_int';
        $attr = 'is_transformed';
        $where = 'is null';
        return $this->getProductCollection($table, $attr, $where);
    }

    /**
     * @param int $entityId
     * @throws InputException
     * @throws LocalizedException
     * @throws NoSuchEntityException
     * @throws StateException
     */
    public function transformProduct(int $entityId)
    {
        $this->storeManager->setCurrentStore(0);
        /** @var Product $product */
        try {
            $product = $this->productRepository->getById($entityId, true, 0, true);
        } catch (\Magento\Framework\Exception\NoSuchEntityException $exception) {
            $this->_logger->warning('Product with ID = ' . $entityId . 'not found');
        }
        if ($product->isDisabled()) {
            return;
        }
        if ($product->getTypeId() == Configurable::TYPE_CODE) {
            if (strpos($product->getName(), 'Chelsa') === false) {
                $this->convertConfigToBundle($product);
            }
        }
        $this->setProductType($product);

        foreach (['returnable' => 'is_returnable', 'tcw' => 'acw'] as $before => $new) {
            $customAttribute = $product->getCustomAttribute($before);
            if ($customAttribute != null) {
                $product->setCustomAttribute($new, $customAttribute);
            }
        }
        $product->setData('is_salable', true);
        $product->setData('on_sale', true);
        $product->setData('is_transformed', true);
        /** Finally! */
        try {
            $this->productRepository->save($product);
            foreach (['youtube', 'video_url'] as $link) {
                $videoUrl = $product->getData($link);
                if ($videoUrl != null) {
                    $this->addVideoToProduct($videoUrl, $product);
                }
            }
        } catch (InputException $inputException) {
            $this->_logger->error($inputException->getMessage());
            throw $inputException;
        } catch (Exception $e) {
            throw new StateException(__('Cannot save product.'));
        }
    }

    /**
     * @param int $productId
     * @throws NoSuchEntityException
     * @throws StateException
     */
    public function deleteProduct(int $productId)
    {
        try {
            $product = $this->productRepository->getById($productId, true, 0);
            $this->productRepository->delete($product);
        } catch (StateException $e) {
            throw new StateException(__('Cannot get product ID = ' . $productId));
        } catch (NoSuchEntityException $e) {
            throw new NoSuchEntityException(__('Cannot delete product ID = ' . $productId));
        }
    }

    /**
     * @param string $table
     * @param string $attr
     * @param string $where
     * @return false|Collection
     */
    protected function getProductCollection(string $table, string $attr, string $where)
    {
        try {
            $needAttr = $this->eav->getAttribute(Product::ENTITY, $attr);
            $productCollection = $this->productCollectionFactory->create();
            $productCollection->getSelect()
                ->reset(Zend_Db_Select::COLUMNS)
                ->columns('entity_id')
                ->joinLeft(
                    ['eav_table' => $table],
                    "`eav_table`.`row_id` = `e`.`row_id` AND
            `eav_table`.`attribute_id` = {$needAttr->getAttributeId()} AND
            `eav_table`.`store_id` = 0",
                    ['value']
                )->where('eav_table.value ' . $where)
                ->where('sku is not null');
            return $productCollection;
        } catch (LocalizedException $e) {
            $this->_logger->critical($e->getMessage());
            return false;
        }
    }

    /**
     * @param Product $product
     * @throws NoSuchEntityException
     */
    protected function setProductType(Product $product)
    {
        if ($product->getData('product_type') == null) {
            $attributeSetName = $this->attrSetRepository->get($product->getAttributeSetId())->getAttributeSetName();
            $productType = $this->mapping->attributeSetToProductType($attributeSetName);
            $product->setCustomAttribute('product_type', $productType);
            if ($product->getCustomAttribute('product_type') == 'Stone') {
                $product->setCustomAttribute('allow_in_bundles', 1);
            }
        }
    }

    /**
     * @param Product $product
     * @param string $videoUrl
     *
     * @throws LocalizedException
     */
    protected function addVideoToProduct($videoUrl, $product)
    {
        $videoProvider = str_replace('https://', '', $videoUrl);
        $videoProvider = str_replace('http://', '', $videoProvider);
        $videoProvider = substr($videoProvider, 0, strpos($videoProvider, "."));
        $videoData = [];
        if ($videoProvider == 'vimeo') {
            $videoData = $this->getFileFromVimeoVideo($videoUrl);
        }

        if ($videoProvider == 'youtube') {
            $this->_logger->info('Youtube video in product SKU' . $product->getSku());
        }
        // Convert video data array to video entry

        $media = $this->externalVideoEntryConverter->convertTo($product, $videoData);
        $this->galleryManagement->create($product->getSku(), $media);
    }

    /**
     * @param Product $product
     * @throws NoSuchEntityException
     * @throws Exception
     */
    protected function convertConfigToBundle(Product $product)
    {
        $product->setData('price_type', TierPriceInterface::PRICE_TYPE_FIXED);
        $this->transformOptionsToBundle($product);
        $this->editProductsFromConfigurable($product);
    }

    /**
     * @param $url
     * @return array
     * @throws FileSystemException
     */
    private function getFileFromVimeoVideo($url)
    {
        $videoData = [];
        $id = substr(strrchr($url, "/"), 1);
        $contentFromVimeo = $this->fileDriver->fileGetContents("http://vimeo.com/api/v2/video/$id.php");
        $fileXml = $this->serializer->unserialize($contentFromVimeo);
        $fileUrl = $fileXml[0]['thumbnail_medium'];
        $imageType = substr(strrchr($fileUrl, "."), 1); //find the image extension
        $filename = $id . '.' . $imageType; //give a new name, you can modify as per your requirement
        try {
            $thumb = $this->curl->execute($fileUrl);
            $this->file->saveFile($filename, $thumb);
            $imageContent = $this->imageContentFactory->create();
            $mimeContentType = $this->mimeTypes[$imageType];
            $imageContent->setName('Migrated video')
                ->setType($mimeContentType)
                ->setBase64EncodedData(base64_encode($thumb));
            // Build video data array for video entry converter
            $generalMediaEntryData = [
                ProductAttributeMediaGalleryEntryInterface::LABEL => 'Migrated video',
                ProductAttributeMediaGalleryEntryInterface::CONTENT => $imageContent,
                ProductAttributeMediaGalleryEntryInterface::DISABLED => false,
                ProductAttributeMediaGalleryEntryInterface::FILE => $filename
            ];
            $videoData = array_merge($generalMediaEntryData, [
                VideoContentInterface::TITLE => 'Migrated Video',
                VideoContentInterface::DESCRIPTION => '',
                VideoContentInterface::PROVIDER => 'vimeo',
                VideoContentInterface::METADATA => null,
                VideoContentInterface::URL => $url,
                VideoContentInterface::TYPE => ExternalVideoEntryConverter::MEDIA_TYPE_CODE,
            ]);
        } catch (FileSystemException $e) {
            return [];
        } catch (LocalizedException $e) {
            return [];
        }
        return $videoData;
    }

    /**
     * @param Product $product
     * @throws Exception
     */
    private function transformOptionsToBundle(Product $product)
    {
        try {
            /** @var ProductExtension $extensionAttributes */
            $extensionAttributes = $product->getExtensionAttributes();
            $productOptions = $extensionAttributes->getConfigurableProductOptions() ?: [];
            $optionsData = $this->mapping->prepareOptionsForBundle($product, $productOptions);
            if ($product->getTypeId() == \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE) {
                $this->converter->toSimple($product, $optionsData, $productOptions);
            }
            if ($product->getTypeId() == \Magento\Catalog\Model\Product\Type::TYPE_BUNDLE) {
                $this->converter->toBundle($product, $optionsData, $productOptions);
            }
        } catch (Exception $e) {
            $this->_logger->error('Transformation error in productID = ' . $product->getId() . ': ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * @param Product $product
     * @throws NoSuchEntityException
     */
    private function editProductsFromConfigurable(Product $product)
    {
        foreach ($this->productFunctionalHelper->getProductForDelete() as $productForDelete) {
            $movedAsPart = 'Removed as part of: ';
            $devTag = $productForDelete->getData('dev_tag');
            if ($devTag !== null && strpos($devTag, $movedAsPart) !== false) {
                $productForDelete->setData('dev_tag', $devTag . ', ' . $product->getId());
            }
            if ($devTag === null) {
                $productForDelete->setData('dev_tag', $movedAsPart . $product->getId());
            }
            $this->setProductType($productForDelete);

            try {
                $this->productRepository->save($productForDelete);
            } catch (CouldNotSaveException $e) {
                $this->_logger->error($e->getMessage());
            } catch (InputException $e) {
                $this->_logger->error($e->getMessage());
            } catch (StateException $e) {
                $this->_logger->error($e->getMessage());
            }
        }
        /** @var ProductExtension $extension */
        $extension = $product->getExtensionAttributes();
        /** Create new extensions */

        $newExtensions = $this->productExtensionFactory->create();
        if ($extension->getBundleProductOptions() !== null) {
            $newExtensions->setBundleProductOptions($extension->getBundleProductOptions());
        }
        if ($extension->getDownloadableProductLinks() !== null) {
            $newExtensions->setDownloadableProductLinks($extension->getDownloadableProductLinks());
        }
        if ($extension->getCategoryLinks() !== null) {
            $newExtensions->setCategoryLinks($extension->getCategoryLinks());
        }
        if ($extension->getDownloadableProductSamples() !== null) {
            $newExtensions->setDownloadableProductSamples($extension->getDownloadableProductSamples());
        }
        if ($extension->getGiftcardAmounts() !== null) {
            $newExtensions->setGiftcardAmounts($extension->getGiftcardAmounts());
        }
        if ($extension->getWebsiteIds() !== null) {
            $newExtensions->setWebsiteIds($extension->getWebsiteIds());
        }
        $stock = $extension->getStockItem();
        if ($stock !== null) {
            /** @var Item $stock */
            $stock->setData('type_id', $product->getTypeId());
            $qty = $product->getQty();
            $stock->setData('qty', $qty);
            if ($qty == 0) {
                $this->_logger->info('Product with SKU = ' . $product->getSku() . ' don\'t have qty in stock');
                $stock->setData('qty', 999);
            }
            $newExtensions->setStockItem($stock);
        }
        $product->setExtensionAttributes($newExtensions);
    }
}
