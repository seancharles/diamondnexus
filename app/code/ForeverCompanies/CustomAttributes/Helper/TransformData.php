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
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\Data\TierPriceInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Gallery\GalleryManagement;
use Magento\Catalog\Model\Product\Option;
use Magento\Catalog\Model\ProductRepository;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\CatalogInventory\Model\Stock\Item;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Eav\Model\Config;
use Magento\Framework\Api\Data\ImageContentInterfaceFactory;
use Magento\Framework\Api\Data\VideoContentInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\StateException;
use Magento\Framework\Serialize\Serializer\Serialize;
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
     * @var MatchingBand
     */
    protected $matchingBand;

    /**
     * @var ImageContentInterfaceFactory
     */
    protected $imageContentFactory;

    /**
     * @var GalleryManagement
     */
    protected $galleryManagement;

    /**
     * @var Serialize
     */
    protected $serializer;

    /**
     * @var ProductType
     */
    protected $productTypeHelper;

    /**
     * @var Media
     */
    protected $mediaHelper;

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
     * @param Context $context
     * @param Config $config
     * @param CollectionFactory $collectionFactory
     * @param ProductRepository $productRepository
     * @param ProductType $productTypeHelper
     * @param ExternalVideoEntryConverter $videoEntryConverter
     * @param Mapping $mapping
     * @param ProductFunctional $productFunctionalHelper
     * @param StoreManagerInterface $storeManager
     * @param Converter $converter
     * @param ProductExtensionFactory $productExtensionFactory
     * @param File $file
     * @param Curl $curl
     * @param MatchingBand $matchingBand
     * @param ImageContentInterfaceFactory $imageContent
     * @param GalleryManagement $galleryManagement
     * @param Media $media
     * @param Serialize $serializer
     */
    public function __construct(
        Context $context,
        Config $config,
        CollectionFactory $collectionFactory,
        ProductRepository $productRepository,
        ProductType $productTypeHelper,
        ExternalVideoEntryConverter $videoEntryConverter,
        Mapping $mapping,
        ProductFunctional $productFunctionalHelper,
        StoreManagerInterface $storeManager,
        Converter $converter,
        ProductExtensionFactory $productExtensionFactory,
        File $file,
        Curl $curl,
        MatchingBand $matchingBand,
        ImageContentInterfaceFactory $imageContent,
        GalleryManagement $galleryManagement,
        Media $media,
        Serialize $serializer
    ) {
        parent::__construct($context);
        $this->eav = $config;
        $this->productCollectionFactory = $collectionFactory;
        $this->productRepository = $productRepository;
        $this->productTypeHelper = $productTypeHelper;
        $this->externalVideoEntryConverter = $videoEntryConverter;
        $this->mapping = $mapping;
        $this->productFunctionalHelper = $productFunctionalHelper;
        $this->storeManager = $storeManager;
        $this->converter = $converter;
        $this->productExtensionFactory = $productExtensionFactory;
        $this->file = $file;
        $this->curl = $curl;
        $this->matchingBand = $matchingBand;
        $this->imageContentFactory = $imageContent;
        $this->galleryManagement = $galleryManagement;
        $this->mediaHelper = $media;
        $this->serializer = $serializer;
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

    public function getProductsForMediaTransformCollection()
    {
        $table = 'catalog_product_entity_int';
        $attr = 'is_media_transformed';
        $where = 'is null';
        return $this->getProductCollection($table, $attr, $where);
    }

    /**
     * @param int $entityId
     */
    public function transformMediaProduct(int $entityId)
    {
        $product = $this->getCurrentProduct($entityId, false);
        if ($product == null) {
            return;
        }
        try {
            $media = $product->getMediaGalleryEntries();
            $options = $product->getOptions();
            $metalTypeOptions = false;
            foreach ($options as $option) {
                if ($option->getTitle() == 'Precious Metal') {
                    /** @var Option $metalTypeOptions */
                    $metalTypeOptions = $option;
                }
            }
            foreach ($media as &$mediaGalleryEntry) {
                $optionLabel = false;
                $role = false;
                if ($mediaGalleryEntry['label'] !== null) {
                    $optionLabel = explode(',', $mediaGalleryEntry['label'])[0] ?? false;
                    $role = explode(',', $mediaGalleryEntry['label'])[1] ?? false;
                }
                if ($metalTypeOptions) {
                    foreach ($metalTypeOptions->getValues() as $id => $option) {
                        if ($optionLabel == $option->getTitle()) {
                            $mediaGalleryEntry['catalog_product_option_type_id'] = $id;
                            continue;
                        }
                    }
                }
                if ($role) {
                    $mediaGalleryEntry->setTypes([strtolower(trim($role)) . '_image']);
                }
            }
            $product->setMediaGalleryEntries($media);
            $product->setData('is_media_transformed', true);
            $this->productRepository->save($product);
            $this->mediaHelper->saveFieldsToMedia($media);
        } catch (LocalizedException $e) {
            $this->_logger->error('Can\'t save media for product id = ' . $entityId);
        }
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
        $product = $this->getCurrentProduct($entityId);
        if ($product == null) {
            return;
        }
        if ($product->getTypeId() == Configurable::TYPE_CODE) {
            if (strpos($product->getName(), 'Chelsa') === false) {
                $this->convertConfigToBundle($product);
            }
        }
        $this->productTypeHelper->setProductType($product);

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
                    $this->addVideoToProduct($videoUrl, $product, $link);
                }
            }
        } catch (InputException $inputException) {
            $this->_logger->error($inputException->getMessage());
            throw $inputException;
        } catch (Exception $e) {
            throw new StateException(__('Cannot save product - ' .$e->getMessage()));
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
     * @param string $videoUrl
     *
     * @param Product $product
     * @param string $videoProvider
     * @throws InputException
     * @throws LocalizedException
     * @throws NoSuchEntityException
     * @throws StateException
     */
    protected function addVideoToProduct($videoUrl, $product, $videoProvider = '')
    {
        /** TODO: all of that functions */
        if (strpos($videoUrl, 'up.diacam360')) {
            throw new StateException(__('Cannot save video from up.diacam360 for product'));
        }
        if (strpos($videoUrl, 's3.amazonaws')) {
            throw new StateException(__('Cannot save video from s3.amazonaws for product'));
        }
        if (strpos($videoUrl, 'assets.stullercloud')) {
            throw new StateException(__('Cannot save video from assets.stullercloud for product'));
        }
        if (strpos($videoUrl, 'v360.in')) {
            throw new StateException(__('Cannot save video from v360.in for product'));
        }
        if ($videoProvider == 'youtube') {
            $videoProvider = 'youtube';
        } else {
            $videoProvider = str_replace('https://', '', $videoUrl);
            $videoProvider = str_replace('http://', '', $videoProvider);
            $dotPosition = strpos($videoProvider, ".") ?? false;
            if ($dotPosition == false ) {
                return;
            }
            $videoProvider = substr($videoProvider, 0, $dotPosition);
        }
        $videoData = $this->getFileFromVimeoVideo($videoUrl, $videoProvider);
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
        /** TODO: Transform all cross-sell products before! */
        $this->transformIncludedProductsFirst($product->getId());
        $product->setData('price_type', TierPriceInterface::PRICE_TYPE_FIXED);
        $this->transformOptionsToBundle($product);
        $this->editProductsFromConfigurable($product);
    }

    /**
     * @param int $entityId
     * @param bool $transformed
     * @return ProductInterface|Product|void|null
     */
    protected function getCurrentProduct(int $entityId, $transformed = true)
    {
        $this->storeManager->setCurrentStore(0);
        /** @var Product $product */
        try {
            $product = $this->productRepository->getById($entityId, true, 0, true);
        } catch (NoSuchEntityException $exception) {
            $this->_logger->warning('Product with ID = ' . $entityId . 'not found');
        }
        if ($product->isDisabled() || $product->getData('is_transformed') == $transformed) {
            return;
        }
        return $product;
    }

    /**
     * @param $url
     * @param string $provider
     * @return array
     * @throws LocalizedException
     */
    private function getFileFromVimeoVideo($url, string $provider = 'vimeo')
    {
        $videoData = [];
        $imageType = '';
        $thumb = '';
        $id = '';
        if ($provider == 'vimeo') {
            $id = substr(strrchr($url, "/"), 1);
            $path = "http://vimeo.com/api/v2/video/$id.php";
            $contentFromVimeo = $this->curl->execute($path);
            $fileXml = $this->serializer->unserialize($contentFromVimeo);
            $fileUrl = $fileXml[0]['thumbnail_medium'];
            $imageType = substr(strrchr($fileUrl, "."), 1); //find the image extension
            $thumb = $this->curl->execute($fileUrl);
        }
        if ($provider == 'youtube') {
            $id = $url;
            $url = 'https://www.youtube.com/watch?v=' . $id;
            $path = "http://img.youtube.com/vi/$id/hqdefault.jpg";
            $thumb = $this->curl->execute($path);
            $imageType = 'jpg';
        }
        $filename = $id . '.' . $imageType; //give a new name, you can modify as per your requirement
        try {
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
            $videoData = array_merge(
                $generalMediaEntryData,
                [
                VideoContentInterface::TITLE => 'Migrated Video',
                VideoContentInterface::DESCRIPTION => '',
                VideoContentInterface::PROVIDER => $provider,
                VideoContentInterface::METADATA => null,
                VideoContentInterface::URL => $url,
                VideoContentInterface::TYPE => ExternalVideoEntryConverter::MEDIA_TYPE_CODE,
                ]
            );
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
            if ($product->getTypeId() == Product\Type::TYPE_SIMPLE) {
                $this->converter->toSimple($product, $optionsData);
            }
            if ($product->getTypeId() == Product\Type::TYPE_BUNDLE) {
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
                continue;
            }
            if ($devTag === null) {
                $productForDelete->setData('dev_tag', $movedAsPart . $product->getId());
            }
            $this->productTypeHelper->setProductType($productForDelete);

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

    /**
     * @param $entityId
     */
    private function transformIncludedProductsFirst($entityId)
    {
        $products = $this->matchingBand->getMatchingBands((int)$entityId);
        if (count($products) > 0) {
            foreach ($products as $product) {
                try {
                    $this->transformProduct((int)$product['product_id']);
                } catch (InputException $e) {
                    $this->_logger->critical('Can\'t transform matching bands for product ID = ' . $entityId);
                } catch (NoSuchEntityException $e) {
                    $this->_logger->critical('Can\'t transform matching bands for product ID = ' . $entityId);
                } catch (StateException $e) {
                    $this->_logger->critical('Can\'t transform matching bands for product ID = ' . $entityId);
                } catch (LocalizedException $e) {
                    $this->_logger->critical('Can\'t transform matching bands for product ID = ' . $entityId);
                }
            }
        }
    }
}
