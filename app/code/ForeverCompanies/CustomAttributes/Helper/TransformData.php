<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace ForeverCompanies\CustomAttributes\Helper;

use Exception;
use ForeverCompanies\CustomAttributes\Logger\ErrorsByOption\Logger as LoggerByOptions;
use ForeverCompanies\CustomAttributes\Logger\ErrorsBySku\Logger as LoggerBySku;
use ForeverCompanies\CustomAttributes\Model\Config\Source\Product\BundleCustomizationType as BType;
use ForeverCompanies\CustomAttributes\Model\Config\Source\Product\CustomizationType;
use Magento\Bundle\Api\Data\LinkInterface;
use Magento\Bundle\Model\Product\Price;
use Magento\Bundle\Model\Product\Type;
use Magento\Bundle\Model\ResourceModel\Selection;
use Magento\Catalog\Api\AttributeSetRepositoryInterface;
use Magento\Catalog\Api\Data\ProductAttributeMediaGalleryEntryInterface;
use Magento\Catalog\Api\Data\ProductCustomOptionInterface;
use Magento\Catalog\Api\Data\ProductExtension;
use Magento\Catalog\Api\Data\ProductExtensionFactory;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\Data\TierPriceInterface;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Gallery\GalleryManagement;
use Magento\Catalog\Model\Product\Option;
use Magento\Catalog\Model\Product\Option\Value;
use Magento\Catalog\Model\ProductRepository;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\CatalogInventory\Model\Stock\Item;
use Magento\CatalogInventory\Model\StockRegistry;
use Magento\CatalogStaging\Model\ResourceModel\ProductSequence;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Eav\Model\AttributeSetRepository;
use Magento\Eav\Model\Config;
use Magento\Framework\Api\Data\ImageContentInterfaceFactory;
use Magento\Framework\Api\Data\VideoContentInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\StateException;
use Magento\Framework\Serialize\Serializer\Serialize;
use Magento\Framework\Validation\ValidationException;
use Magento\InventoryApi\Api\Data\SourceItemInterfaceFactory;
use Magento\InventoryApi\Api\SourceItemsSaveInterface;
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
     * @var \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory
     */
    protected $categoryCollectionFactory;

    /**
     * @var Category
     */
    protected $category;

    /**
     * @var \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory
     */
    protected $attributeSetCollectionFactory;

    /**
     * @var StockRegistry
     */
    protected $stockRegistry;

    /**
     * @var SourceItemsSaveInterface
     */
    protected $sourceItemsSaveInterface;

    /**
     * @var SourceItemInterfaceFactory
     */
    protected $sourceItemFactory;

    /**
     * @var Config
     */
    protected $eav;

    /**
     * @var AttributeSetRepository
     */
    protected $attributeSetRepository;

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

    /**
     * @var Selection
     */
    protected $bundleSelection;

    /**
     * @var LoggerByOptions
     */
    protected $loggerByOptions;

    /**
     * @var LoggerBySku
     */
    protected $loggerBySku;

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
     * @var string[]
     */
    protected $configurableSku = [
        'LRENRS0105XCU',
        'LRENRS0105XRB',
        'MRWBXX0054X',
        'MRWBXX0053X',
        'MRWBXX0082X',
        'MRWBXX0081X',
        'MRWBXX0080X',
        'MRWBXX0079X',
        'MRWBXX0078X',
        'MRWBXX0077X',
        'MRWBXX0073X',
        'MRWBXX0072X',
        'MRWBXX0071X',
        'MRWBXX0076X',
        'MRWBXX0075X',
        'MRWBXX0074X'
    ];

    /**
     * @param Context $context
     * @param Config $config
     * @param AttributeSetRepository $attributeSetRepository
     * @param CollectionFactory $collectionFactory
     * @param \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory $attributeSetCollectionFactory
     * @param StockRegistry $stockRegistry
     * @param SourceItemsSaveInterface $sourceItemsSaveInterface
     * @param SourceItemInterfaceFactory $sourceItemFactory
     * @param Category $category
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
     * @param Selection $bundleSelection
     * @param LoggerByOptions $loggerByOptions
     * @param LoggerBySku $loggerBySku
     */
    public function __construct(
        Context $context,
        Config $config,
        AttributeSetRepository $attributeSetRepository,
        CollectionFactory $collectionFactory,
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory $attributeSetCollectionFactory,
        StockRegistry $stockRegistry,
        SourceItemsSaveInterface $sourceItemsSaveInterface,
        SourceItemInterfaceFactory $sourceItemFactory,
        Category $category,
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
        Serialize $serializer,
        Selection $bundleSelection,
        LoggerByOptions $loggerByOptions,
        LoggerBySku $loggerBySku
    ) {
        parent::__construct($context);
        $this->eav = $config;
        $this->attributeSetRepository = $attributeSetRepository;
        $this->productCollectionFactory = $collectionFactory;
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        $this->category = $category;
        $this->attributeSetCollectionFactory = $attributeSetCollectionFactory;
        $this->stockRegistry = $stockRegistry;
        $this->sourceItemsSaveInterface = $sourceItemsSaveInterface;
        $this->sourceItemFactory = $sourceItemFactory;
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
        $this->bundleSelection = $bundleSelection;
        $this->loggerByOptions = $loggerByOptions;
        $this->loggerBySku = $loggerBySku;
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
     * @param string $sku
     */
    public function updateStock($sku)
    {
        try {
            $stock = $this->stockRegistry->getStockItemBySku($sku);
            $stock->setQty(999999);
            $this->stockRegistry->updateStockItemBySku($sku, $stock);
            $sourceItem = $this->sourceItemFactory->create();
            $sourceItem->setSourceCode('default');
            $sourceItem->setSku((string)$sku);
            $sourceItem->setQuantity(999999);
            $sourceItem->setStatus(1);
            $this->sourceItemsSaveInterface->execute([$sourceItem]);
        } catch (NoSuchEntityException $e) {
            $this->_logger->error('Can\'t update stocks for product SKU = ' . $sku . ': ' . $e->getMessage());
        } catch (CouldNotSaveException $e) {
            $this->_logger->error('Can\'t update stocks for product SKU = ' . $sku . ': ' . $e->getMessage());
        } catch (InputException $e) {
            $this->_logger->error('Can\'t update stocks for product SKU = ' . $sku . ': ' . $e->getMessage());
        } catch (ValidationException $e) {
            $this->_logger->error('Can\'t update stocks for product SKU = ' . $sku . ': ' . $e->getMessage());
        }
    }

    /**
     * @param string $sku
     */
    public function updateLooseStone($sku)
    {
        $changeFlag = false;
        try {
            $product = $this->productRepository->get($sku);
            if ($product->getData('gemstone') == null || $product->getData('gemstone') == '') {
                $centerStoneSize = substr_replace(substr($sku, 17, 4), '.', 2, 0) . ' ct';
                if (strpos($centerStoneSize, '0') === 0) {
                    $centerStoneSize = substr($centerStoneSize, 1);
                }
                $gemstoneAttribute = $this->eav->getAttribute(Product::ENTITY, 'gemstone');
                $centerStoneSizeValue = $gemstoneAttribute->getSource()->getOptionId($centerStoneSize);
                $product->setData('gemstone', $centerStoneSizeValue);
                $changeFlag = true;
            }
            if ($product->getData('carat_weight') == null || $product->getData('carat_weight') == 0) {
                $caratWeight = substr_replace(substr($sku, 17, 4), '.', 2, 0);
                if (strpos($caratWeight, '0') === 0) {
                    $caratWeight = substr($caratWeight, 1);
                }
                $product->setData('carat_weight', (float)$caratWeight);
                $changeFlag = true;
            }
            if ($changeFlag) {
                $this->productRepository->save($product);
            }
        } catch (NoSuchEntityException $e) {
            $this->_logger->error($e->getMessage());
        } catch (LocalizedException $e) {
            $this->_logger->error($e->getMessage());
        }
    }

    /**
     * @param string $sku
     */
    public function updateRingSizeSku($sku)
    {
        $changeFlag = false;
        try {
            $product = $this->productRepository->get($sku);
            $options = $product->getOptions();
            if (count($options) == 0) {
                return;
            }
            foreach ($options as $option) {
                if ($option->getTitle() == 'Ring Size') {
                    $values = $option->getValues();
                    foreach ($values as &$value) {
                        if (strlen($value->getSku()) == 3) {
                            $value->setSku('0' . $value->getSku());
                        }
                    }
                    $option->setValues($values);
                    $changeFlag = 1;
                }
            }
            if ($changeFlag) {
                $this->productRepository->save($product);
            }
        } catch (NoSuchEntityException $e) {
            $this->_logger->error($e->getMessage());
        } catch (LocalizedException $e) {
            $this->_logger->error($e->getMessage());
        }
    }

    /**
     * @return Collection
     */
    public function getProductsForChangeStocks()
    {
        $categories = $this->getCategoriesAndSubcategories('Clearance');
        $attributeSetId = $this->getAttributeSetId('Migration_Loose Diamonds');
        $collection = $this->productCollectionFactory->create();
        $collection->addAttributeToSelect('*');
        $collection->addAttributeToFilter('status', Status::STATUS_ENABLED);
        $collection->addAttributeToFilter('attribute_set_id', ['neq' => $attributeSetId]);
        $collection->addCategoriesFilter(['nin' => $categories]);
        //only filter in stock product
        return $collection;
    }

    /**
     * @return Collection
     */
    public function getMigrationLooseDiamondsProducts()
    {
        $attributeSetId = $this->getAttributeSetId('Migration_Loose Diamonds');
        $collection = $this->productCollectionFactory->create();
        $collection->addAttributeToSelect('*');
        $collection->addAttributeToFilter('status', Status::STATUS_ENABLED);
        $collection->addAttributeToFilter('attribute_set_id', ['eq' => $attributeSetId]);
        return $collection;
    }

    /**
     * @param $sku
     */
    public function cleanOptions($sku)
    {
        try {
            $product = $this->productRepository->get($sku);

            $flag = false;
            $options = $product->getOptions();
            if (count($options) > 0) {
                foreach ($options as $id => $option) {
                    if (strpos('Set', $option->getTitle()) !== false) {
                        unset($options[$id]);
                        $flag = true;
                    }
                }
                if ($flag) {
                    $product->setOptions($options);
                    $this->productRepository->save($product);
                }
            }
        } catch (NoSuchEntityException $e) {
            $this->_logger->error('Can\'t get product SKU =  ' . $sku . ': ' . $e->getMessage());
        } catch (CouldNotSaveException $e) {
            $this->_logger->error('Can\'t clean options for product SKU =  ' . $sku . ': ' . $e->getMessage());
        } catch (InputException $e) {
            $this->_logger->error('Can\'t clean options for product SKU =  ' . $sku . ': ' . $e->getMessage());
        } catch (StateException $e) {
            $this->_logger->error('Can\'t clean options for product SKU =  ' . $sku . ': ' . $e->getMessage());
        }
    }

    /**
     * @return Collection
     */
    public function getProductsForChangeLooseStones()
    {
        $attributeSetId = $this->getAttributeSetId('Migration_Loose Stones');
        $collection = $this->productCollectionFactory->create();
        $collection->addAttributeToSelect('*');
        $collection->addAttributeToFilter('status', Status::STATUS_ENABLED);
        $collection->addAttributeToFilter('attribute_set_id', ['eq' => $attributeSetId]);
        $collection->addAttributeToFilter('type_id', ['eq' => Product\Type::TYPE_SIMPLE]);
        return $collection;
    }

    public function getBundleProducts()
    {
        $collection = $this->productCollectionFactory->create();
        $collection->addAttributeToSelect('*');
        $collection->addAttributeToFilter('status', Status::STATUS_ENABLED);
        $collection->addAttributeToFilter('type_id', ['eq' => Product\Type::TYPE_BUNDLE]);
        return $collection;
    }

    /**
     * @return Collection
     */
    public function getProductsForDisableCollection()
    {
        $table = 'catalog_product_entity_varchar';
        $attr = 'dev_tag';
        $where = 'like "%Removed as part of%" || eav_table.value like "%Migrated to%"';
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
     * @return Collection
     */
    public function getProductsAfterTransformCollection()
    {
        $table = 'catalog_product_entity_int';
        $attr = 'is_transformed';
        $where = '= 1';
        return $this->getProductCollection($table, $attr, $where);
    }

    /**
     * @return false|Collection
     */
    public function getProductsForMediaTransformCollection()
    {
        $table = 'catalog_product_entity_int';
        $attr = 'is_media_transformed';
        $where = '= 0';
        return $this->getProductCollection($table, $attr, $where);
    }

    /**
     * @param int $productId
     */
    public function transformProductSelect(int $productId)
    {
        try {
            $entity = $this->productRepository->getById($productId);
            /** @var Option $option */
            $entityOptions = $entity->getOptions();
            foreach ($entityOptions as $option) {
                $attributeCode = $option->getData('customization_type');
                $entity->unlockAttribute($attributeCode);
                $data = [];
                $eav = $this->eav->getAttribute(Product::ENTITY, $attributeCode);
                if ($option->getValues() == null) {
                    $this->loggerByOptions->error('Can\'t find options for Product ID = ' . $productId);
                    continue;
                }
                foreach ($option->getValues() as $value) {
                    $data[] = $this->getDataForMultiselectable($value, $eav->getOptions());
                }
                $data = array_filter(array_unique($data));
                $dataString = empty($data) ? '' : implode(',', $data);
                $entity->setData($attributeCode, $dataString);
            }
            $this->productRepository->save($entity);
        } catch (NoSuchEntityException $e) {
            $this->_logger->error('Can\'t transform product select for ' . $productId . ': ' . $e->getMessage());
        } catch (LocalizedException $e) {
            $this->_logger->error('Can\'t transform product select for ' . $productId . ': ' . $e->getMessage());
        }
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
     */
    public function transformProductOptions(int $entityId)
    {
        $missingOptions = [];

        try {
            $product = $this->getCurrentProduct($entityId, false);
            if ($product == null) {
                return;
            }
            if ($product->getData('product_type') == null) {
                $this->productTypeHelper->setProductType($product);
            }
            $options = $product->getOptions();
            $isBundle = $product->getTypeId();
            if ($options == null && $isBundle != Type::TYPE_CODE) {
                return;
            }
            $oldAttributes = ['metal_type' => 'filter_metal', 'shape' => 'filter_shape', 'color' => 'filter_color'];
            if ($options !== null) {
                $certifiedStone = true;
                /** @var Option $option */
                foreach ($options as &$option) {
                    if ($option->getData('customization_type') !== null) {
                        continue;
                    }
                    $customizationType = $this->setCustomizationTypeToOption($option->getTitle());
                    foreach ($oldAttributes as $attribute => $bool) {
                        if ($customizationType == $attribute) {
                            unset($oldAttributes[$attribute]);
                            continue;
                        }
                    }
                    if ($customizationType == -1) {
                        $missingOptions[] = $option->getTitle();
                        $customizationType = '';
                    }
                    $option['customization_type'] = $customizationType;
                    if ($option['title'] == 'Certified Stone') {
                        $certifiedStone = false;
                    }
                }
                $product->setOptions($options);
            }
            foreach ($oldAttributes as $attribute => $filter) {
                $data = $product->getData($filter);
                if ($data == null) {
                    continue;
                }
                $newValues = $this->converter->getValues(explode(',', $data), $filter);
                if (count($newValues) > 0) {
                    $newOptionsId = $this->converter->getOptions($newValues, $attribute);
                    $newValue = implode(',', $newOptionsId);
                    if ($newValue == $product->getData($attribute)) {
                        continue;
                    }
                    if ($product->getData($attribute) == '' || $product->getData($attribute) == null) {
                        $allOptions = $newValue;
                    } else {
                        $allOptions = $product->getData($attribute) . ',' . $newValue;
                    }
                    $product->setData($attribute, $allOptions);
                }
            }
            if ($isBundle == Type::TYPE_CODE) {
                if (isset($certifiedStone)) {
                    $this->bundleOptions($product, $certifiedStone);
                } else {
                    $this->bundleOptions($product);
                }
            }

            if (count($missingOptions) > 0) {
                $sku = $product->getSku();
                $id = $product->getId();
                $start = "Missing options SKU: ";
                $msg = $start . $sku . " | ID: " . $id . " | Options: {" . implode("|", $missingOptions) . "}\n";
                $this->loggerBySku->error($msg);
                foreach ($missingOptions as $opt) {
                    $this->loggerByOptions->error($opt);
                }
            }
            $this->productRepository->save($product);
        } catch (CouldNotSaveException $e) {
            $this->_logger->error('Error in transform options for ID = ' . $entityId . ': ' . $e->getMessage());
        } catch (InputException $e) {
            $this->_logger->error('Error in transform options for ID = ' . $entityId . ': ' . $e->getMessage());
        } catch (StateException $e) {
            $this->_logger->error('Error in transform options for ID = ' . $entityId . ': ' . $e->getMessage());
        } catch (LocalizedException $e) {
            $this->loggerByOptions->error('Can\'t transform option for certificated product ID = ' . $entityId);
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
        if ($product->getName() == null) {
            $this->_logger->error('Product ID = ' . $entityId . ' without name');
            return;
        }
        if ($product->getTypeId() == Configurable::TYPE_CODE) {
            $sku = $product->getSku();
            if (strpos($product->getName(), 'Chelsa') === false && !in_array($sku, $this->configurableSku)) {
                if (strpos($sku, 'LREB') === false) {
                    $this->convertConfigToBundle($product);
                }
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
        $product->setCustomAttribute('is_transformed', true);
        $product->setData('sku_type', 1);
        $product->setData('weight_type', 1);
        $product->setData('price_type', 1);
        if ($product->getData('certified_stone') !== null) {
            $certifiedSrc = $this->eav->getAttribute(Product::ENTITY, 'certified_stone')->getSource();
            $optionText = $product->getData('certified_stone') ? 'Classic Stone' : 'Certified Stone';
            $value = $certifiedSrc->getOptionId($optionText);
            $product->setData('certified_stone', $value);
            $product->setCustomAttribute('certified_stone', $value);
        }
        /** Finally! */
        try {
            if ($product->getTypeId() == Product\Type::TYPE_BUNDLE) {
                $this->refreshOptions($product);
            }
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
            throw new StateException(__('Cannot save product - ' . $e->getMessage()));
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
            throw new StateException(__('Cannot get product ID = ' . $productId . ': ' . $e->getMessage()));
        } catch (NoSuchEntityException $e) {
            throw new NoSuchEntityException(__('Cannot delete product ID = ' . $productId));
        }
    }

    /**
     * @param int $productId
     * @throws NoSuchEntityException
     * @throws StateException
     */
    public function disableProduct(int $productId)
    {
        try {
            $product = $this->productRepository->getById($productId, true, 0);
            if ($product->getTypeId() == Configurable::TYPE_CODE) {
                $product->setStatus(Status::STATUS_DISABLED);
                $this->productRepository->save($product);
                return;
            }
            $parentIds = $this->bundleSelection->getParentIdsByChild($productId);
            if ($parentIds !== null && count($parentIds) !== 0) {
                $this->_logger->info('SPECIAL INFO FOR STEVE Z: product ID = ' . $productId . ' include in bundle');
                return;
            }
            $product->setStatus(Status::STATUS_DISABLED);
            $this->productRepository->save($product);
        } catch (StateException $e) {
            throw new StateException(__('Cannot get product ID = ' . $productId));
        } catch (NoSuchEntityException $e) {
            throw new NoSuchEntityException(__('Cannot delete product ID = ' . $productId));
        } catch (CouldNotSaveException $e) {
            $this->_logger->error('Can\'t disable product ID = ' . $productId . ': ' . $e->getMessage());
        } catch (InputException $e) {
            $this->_logger->error('Can\'t disable product ID = ' . $productId . ': ' . $e->getMessage());
        }
    }

    /**
     * @param int $entityId
     * @throws InputException
     * @throws LocalizedException
     * @throws StateException
     */
    public function updateBundlePriceTypeFixed(int $entityId)
    {
        $product = $this->getCurrentProduct($entityId);

        // if product not found, or product name not set, log error and return
        if ($product == null) {
            return;
        }
        if ($product->getName() == null) {
            $this->_logger->error('Product ID = ' . $entityId . ' without name');
            return;
        }

        // if product is not a bundle, log error and return
        if ($product->getTypeId() !== Product\Type::TYPE_BUNDLE) {
            $this->_logger->error('updateBundlePriceType Error: Product ID = ' . $entityId . ' is not a bundle.');
            return;
        }

        // set price_type
        $product->setData('price_type', Price::PRICE_TYPE_FIXED);

        // save the product
        try {
            $this->productRepository->save($product);
        } catch (InputException $inputException) {
            $this->_logger->error($inputException->getMessage());
            throw $inputException;
        } catch (Exception $e) {
            throw new StateException(__('Cannot save product - ' . $e->getMessage()));
        }
    }

    /**
     * @param Product $product
     */
    protected function refreshOptions(Product $product)
    {
        $options = $product->getOptions();
        /** @var ProductExtension $extensionAttributes */
        $extensionAttributes = $product->getExtensionAttributes();
        $bundleOptions = $extensionAttributes->getBundleProductOptions();
        if ($bundleOptions == null) {
            $product->setTypeId(Product\Type::TYPE_SIMPLE);
            return;
        }
        /** @var Option $option */
        foreach ($options as $id => $option) {
            /** @var \Magento\Bundle\Model\Option $bundleOption */
            foreach ($bundleOptions as $bundleOption) {
                if ($option->getTitle() == $bundleOption->getTitle()) {
                    unset($options[$id]);
                }
            }
        }
        $product->setOptions($options);
    }

    /**
     * @param $title
     * @param $links
     * @param bool $classicStone
     * @return bool
     */
    protected function checkClassicStone($title, $links, $classicStone)
    {
        if ($title == 'Center Stone Size') {
            foreach ($links as $link) {
                if (substr($link['sku'], 23, 1) == '1') {
                    return false;
                }
            }
        }
        return $classicStone;
    }

    /**
     * @param Value $value
     * @param array $options
     * @return mixed|string
     */
    protected function getDataForMultiselectable(Value $value, array $options)
    {
        /** @var \Magento\Eav\Model\Entity\Attribute\Option $option */
        foreach ($options as $option) {
            $title = $value->getTitle();
            if ($title == $option['label']) {
                return $option['value'];
            }
            if ($title == 'Round Brilliant') {
                $title = 'Round';
                if ($title == $option['label']) {
                    return $option['value'];
                }
            }
        }
        return '';
    }

    /**
     * @param $product
     * @param bool|null $certifiedStone
     */
    protected function bundleOptions($product, $certifiedStone = null)
    {
        $bundleOptions = $product->getExtensionAttributes()->getBundleProductOptions();
        if ($certifiedStone !== null && $certifiedStone === true) {
            $classicStone = true;
        }
        $matchingBand = false;
        if ($bundleOptions !== null) {
            foreach ($bundleOptions as &$bundleData) {
                if ($bundleData->getData('bundle_customization_type') !== null) {
                    continue;
                }
                $title = $bundleData->getTitle();
                $productLinks = $bundleData->getProductLinks();
                if (count($productLinks) > 0) {
                    $matchingBand = true;
                    if (isset($classicStone)) {
                        $classicStone = $this->checkClassicStone($title, $productLinks, $classicStone);
                    }
                }
                if ($title == 'Ring Size:') {
                    $title = 'Ring Size';
                }
                $bundleData['bundle_customization_type'] = BType::OPTIONS[BType::TITLE_MAPPING[$title]];
            }
        }
        $product->setData('bundle_options_data', $bundleOptions);
        if (isset($classicStone)) {
            try {
                $src = $this->eav->getAttribute(Product::ENTITY, 'certified_stone')->getSource();
                $optionText = $classicStone ? 'Classic Stone' : 'Certified Stone';
                $value = (int)$src->getOptionId($optionText);
                $product->setData('certified_stone', $value);
            } catch (LocalizedException $e) {
                $this->_logger->error($e->getMessage());
            }
        }
        try {
            $matchingSrc = $this->eav->getAttribute(Product::ENTITY, 'matching_band')->getSource();

            if ($matchingBand) {
                $product->setData('matching_band', (int)$matchingSrc->getOptionId('Yes'));
            } else {
                $product->setData('matching_band', (int)$matchingSrc->getOptionId('None'));
            }
        } catch (LocalizedException $e) {
            $this->_logger->error($e->getMessage());
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
            $productCollection->addAttributeToFilter('status', Status::STATUS_ENABLED);
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
        /** For all of that videos we will need create thumbs */
        $updiacam = strpos($videoUrl, 'up.diacam360');
        if ($updiacam) {
            throw new StateException(__('Cannot save video from up.diacam360 for product'));
        }
        $amazonaws = strpos($videoUrl, 's3.amazonaws');
        if ($amazonaws) {
            throw new StateException(__('Cannot save video from s3.amazonaws for product'));
        }
        $stullercloud = strpos($videoUrl, 'assets.stullercloud');
        if ($stullercloud) {
            /*$videoData = $this->getFileFromVimeoVideo($videoUrl, 'assets.stullercloud');
            $media = $this->externalVideoEntryConverter->convertTo($product, $videoData);
            $this->galleryManagement->create($product->getSku(), $media);
            return;*/
            throw new StateException(__('Cannot save video from assets.stullercloud for product'));
        }
        $storage = strpos($videoUrl, 'storage.solofordiamonds');
        if ($storage) {
            throw new StateException(__('Cannot save video from storage.solofordiamonds for product'));
        }
        $v360 = strpos($videoUrl, 'v360.in');
        if ($v360) {
            throw new StateException(__('Cannot save video from v360.in for product'));
        }
        if ($videoProvider == 'youtube') {
            $videoProvider = 'youtube';
        }
        if ($videoProvider == 'vimeo') {
            $videoProvider = str_replace('https://', '', $videoUrl);
            $videoProvider = str_replace('http://', '', $videoProvider);
            $dotPosition = strpos($videoProvider, ".") ?? false;
            if ($dotPosition == false) {
                return;
            }
            $videoProvider = substr($videoProvider, 0, $dotPosition);
        }
        if ($videoProvider == 'vimeo' || $videoProvider == 'youtube') {
            $videoData = $this->getFileFromVimeoVideo($videoUrl, $videoProvider);
            // Convert video data array to video entry

            $media = $this->externalVideoEntryConverter->convertTo($product, $videoData);
            $this->galleryManagement->create($product->getSku(), $media);
        } else {
            $this->_logger->error('Can\'t save video for ' . $product->getId() . ' from ' . $videoProvider);
        }
    }

    /**
     * @param string $category
     * @return array
     */
    protected function getCategoriesAndSubcategories(string $category)
    {
        try {
            $collection = $this->categoryCollectionFactory->create()->addAttributeToFilter('name', $category);

            /** @var Category $needCategory */
            $needCategory = $collection->getFirstItem();
            return $needCategory->getAllChildren(true);
        } catch (LocalizedException $e) {
            return [];
        }
    }

    /**
     * @param string $setName
     * @return array|mixed|null
     */
    protected function getAttributeSetId(string $setName)
    {
        $set = $this->attributeSetCollectionFactory->create()->addFieldToFilter('attribute_set_name', $setName);
        return $set->getFirstItem()->getData('attribute_set_id');
    }

    /**
     * @param Product $product
     * @throws NoSuchEntityException
     * @throws Exception
     */
    protected function convertConfigToBundle(Product $product)
    {
        $this->transformIncludedProductsFirst($product->getId(), $product->getSku());
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

        if ($product->getData('is_transformed') === $transformed || $product->isDisabled()) {
            return;
        }
        return $product;
    }

    /**
     * @param $title
     * @return string
     */
    protected function setCustomizationTypeToOption($title)
    {
        switch ($title) {
            case 'Precious Metal':
            case 'Metal Type':
            case 'Metal':
                return CustomizationType::OPTIONS['Metal Type'];
            default:
                if (!array_key_exists(trim($title), CustomizationType::TITLE_MAPPING)) {
                    return -1;
                }
                return CustomizationType::OPTIONS[CustomizationType::TITLE_MAPPING[trim($title)]];
        }
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
        /*if ($provider == 'assets.stullercloud') {
            $id = substr(strrchr($url, "/"), 2);
            $id = str_replace('.mp4', '', $id);
            $contentFromVimeo = $this->curl->execute($url);
            $generalMediaEntryData = [
                ProductAttributeMediaGalleryEntryInterface::LABEL => 'Migrated video',
                ProductAttributeMediaGalleryEntryInterface::CONTENT => '',
                ProductAttributeMediaGalleryEntryInterface::DISABLED => false,
                ProductAttributeMediaGalleryEntryInterface::FILE => ''
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
            return $videoData;
        }*/
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
        $sku = $product->getSku();
        foreach ($this->productFunctionalHelper->getProductForDelete() as $productForDelete) {
            $attributeSet = $this->attributeSetRepository->get($productForDelete->getAttributeSetId());
            if ($attributeSet->getAttributeSetName() == 'Migration_Loose Stones') {
                continue;
            }
            if (in_array($sku, $this->configurableSku) || strpos($sku, 'LREB') === 0) {
                continue;
            }
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
                $this->_logger->info('Product with SKU = ' . $sku . ' don\'t have qty in stock');
                $stock->setData('qty', 999);
            }
            $newExtensions->setStockItem($stock);
        }
        $product->setExtensionAttributes($newExtensions);
    }

    /**
     * @param $entityId
     * @param $sku
     */
    private function transformIncludedProductsFirst($entityId, $sku)
    {
        try {
            $products = $this->matchingBand->getMatchingBands((int)$entityId);
            if (count($products) > 0) {
                foreach ($products as $product) {
                    $this->transformProduct((int)$product['product_id']);
                }
            }
            $products = $this->productRepository->get($sku)->getExtensionAttributes()->getConfigurableProductLinks();
            if (count($products) > 0) {
                foreach ($products as $product) {
                    $this->transformProduct((int)$product);
                }
            }
            if ($sku == 'LRENSL0091X') {
                $products = $this->matchingBand->getEnhancers((int)$entityId);
                if (count($products) > 0) {
                    foreach ($products as $product) {
                        $this->transformProduct((int)$product['product_id']);
                    }
                }
            }
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
