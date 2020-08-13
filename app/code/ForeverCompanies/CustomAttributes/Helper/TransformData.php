<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace ForeverCompanies\CustomAttributes\Helper;

use Magento\Bundle\Api\Data\LinkInterface;
use Magento\Bundle\Api\Data\LinkInterfaceFactory;
use Magento\Bundle\Api\Data\OptionInterfaceFactory;
use Magento\Bundle\Model\Product\Type;
use Magento\Catalog\Api\AttributeSetRepositoryInterface;
use Magento\Catalog\Api\Data\ProductCustomOptionInterface;
use Magento\Catalog\Api\Data\ProductCustomOptionInterfaceFactory;
use Magento\Catalog\Api\Data\ProductExtension;
use Magento\Catalog\Api\Data\TierPriceInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Option;
use Magento\Catalog\Model\ProductRepository;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Eav\Model\Config;
use Magento\Framework\Api\Data\VideoContentInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\StateException;
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
     * @var ExternalVideoEntryConverter
     */
    private $externalVideoEntryConverter;

    /**
     * @var OptionInterfaceFactory
     */
    private $optionInterfaceFactory;

    /**
     * @var ProductCustomOptionInterfaceFactory
     */
    private $productCustomOptionInterfaceFactory;

    /**
     * @param Context $context
     * @param Config $config
     * @param CollectionFactory $collectionFactory
     * @param ProductRepository $productRepository
     * @param AttributeSetRepositoryInterface $attributeSetRepository
     * @param ExternalVideoEntryConverter $videoEntryConverter
     * @param OptionInterfaceFactory $optionInterfaceFactory
     * @param ProductCustomOptionInterfaceFactory $productCustomOptionInterfaceFactory
     * @param Mapping $mapping
     * @param ProductFunctional $productFunctionalHelper
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Context $context,
        Config $config,
        CollectionFactory $collectionFactory,
        ProductRepository $productRepository,
        AttributeSetRepositoryInterface $attributeSetRepository,
        ExternalVideoEntryConverter $videoEntryConverter,
        OptionInterfaceFactory $optionInterfaceFactory,
        ProductCustomOptionInterfaceFactory $productCustomOptionInterfaceFactory,
        Mapping $mapping,
        ProductFunctional $productFunctionalHelper,
        StoreManagerInterface $storeManager
    )
    {
        parent::__construct($context);
        $this->eav = $config;
        $this->productCollectionFactory = $collectionFactory;
        $this->productRepository = $productRepository;
        $this->attrSetRepository = $attributeSetRepository;
        $this->externalVideoEntryConverter = $videoEntryConverter;
        $this->optionInterfaceFactory = $optionInterfaceFactory;
        $this->productCustomOptionInterfaceFactory = $productCustomOptionInterfaceFactory;
        $this->mapping = $mapping;
        $this->productFunctionalHelper = $productFunctionalHelper;
        $this->storeManager = $storeManager;
    }

    /**
     * @return Collection
     * @throws LocalizedException
     */
    public function getProductsForTransformCollection()
    {
        $isTransformedAttr = $this->eav->getAttribute(Product::ENTITY, 'is_transformed');
        $productCollection = $this->productCollectionFactory->create();
        $productCollection->getSelect()
            ->reset(Zend_Db_Select::COLUMNS)
            ->columns('entity_id')
            ->joinLeft(
                ['eav_int' => 'catalog_product_entity_int'],
                "`eav_int`.`row_id` = `e`.`row_id` AND
            `eav_int`.`attribute_id` = {$isTransformedAttr->getAttributeId()} AND
            `eav_int`.`store_id` = 0",
                ['value']
            )->where('eav_int.value is null');
        return $productCollection;
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
        $entityId = 9599; //Need delete it after testing
        $this->storeManager->setCurrentStore(0);
        $product = $this->productRepository->getById($entityId, true, 0, true);
        if ($product->getTypeId() == Configurable::TYPE_CODE) {
            if (strpos($product->getName(), 'Chelsa') === false) {
                $this->convertConfigToBundle($product);
            }
        }
        $this->setProductType($product);
        foreach (['youtube', 'video_url'] as $link) {
            $videoUrl = $product->getCustomAttribute($link);
            if ($videoUrl != null) {
                $this->addVideoToProduct($videoUrl, $product);
            }
        }
        foreach (['returnable' => 'is_returnable', 'tcw' => 'acw'] as $before => $new) {
            $customAttribute = $product->getCustomAttribute($before);
            if ($customAttribute != null) {
                $product->setCustomAttribute($new, $customAttribute);
            }
        }
        $product->setData('is_transformed', 1);
        /** Finally! */
        try {
            $this->productRepository->save($product);
        } catch (InputException $inputException) {
            var_dump($inputException);
            exit; //delete it after testing
            throw $inputException;
        } catch (\Exception $e) {
            var_dump($e);
            exit; //delete it after testing too
            throw new StateException(__('Cannot save product.'));
        }
        exit('stop test');
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
        $videoProvider = substr($videoUrl, 0, strpos($videoProvider, "."));
        $videoData = [
            VideoContentInterface::TITLE => 'Migrated Video',
            VideoContentInterface::DESCRIPTION => '',
            VideoContentInterface::PROVIDER => $videoProvider,
            VideoContentInterface::METADATA => null,
            VideoContentInterface::URL => $videoUrl,
            VideoContentInterface::TYPE => ExternalVideoEntryConverter::MEDIA_TYPE_CODE,
        ];
        // Convert video data array to video entry
        $media = $this->externalVideoEntryConverter->convertTo($product, $videoData);
        $product->setMediaGalleryEntries([$media]);
    }

    /**
     * @param Product $product
     * @throws NoSuchEntityException
     */
    protected function convertConfigToBundle(Product $product)
    {
        $product->setData('price_type', TierPriceInterface::PRICE_TYPE_FIXED);
        $this->transformOptionsToBundle($product);
        $this->editProductsFromConfigurable($product);
        $product->setTypeId(Type::TYPE_CODE);
    }

    /**
     * @param Product $product
     */
    private function transformOptionsToBundle(Product $product)
    {
        /** @var ProductExtension $extensionAttributes */
        $extensionAttributes = $product->getExtensionAttributes();
        $productOptions = $extensionAttributes->getConfigurableProductOptions() ?: [];
        $optionsData = $this->mapping->prepareOptionsForBundle($product, $productOptions);
        $product->setData('bundle_options_data', $optionsData['bundle']);
        $options = [];
        foreach ($product->getData('bundle_options_data') as $key => $optionData) {
            /** @var Option $option */
            $option = $this->productCustomOptionInterfaceFactory->create();
            $attributeId = $this->mapping->getAttributeIdFromProductOptions($productOptions, $optionData['title']);
            $option->setData(
                [
                    'price_type' => TierPriceInterface::PRICE_TYPE_FIXED,
                    'title' => $optionData['title'],
                    'type' => ProductCustomOptionInterface::OPTION_TYPE_DROP_DOWN,
                    'is_require' => 1,
                    'values' => $optionsData['options'][$attributeId],
                    'product_sku' => $product->getSku(),
                ]
            );
            $options[] = $option;
        }
        $product->setOptions($options);
        if (isset($optionsData['links'])) {
            /** @var \Magento\Bundle\Model\Option $bundleOption */
            $bundleOption = $this->optionInterfaceFactory->create();
            $bundleOption->setData([
                'title' => 'Center Stone Size',
                'type' => 'select',
                'required' => '1',
                'product_links' => $optionsData['links']
            ]);
            $extensionAttributes->setBundleProductOptions([$bundleOption]);
            $product->setExtensionAttributes($extensionAttributes);
        }

    }

    /**
     * @param Product $product
     * @throws NoSuchEntityException
     */
    private function editProductsFromConfigurable(Product $product)
    {
        foreach ($this->productFunctionalHelper->getProductForDelete() as $productForDelete) {
            $productForDelete->setVisibility(false);
            $productForDelete->setStatus(Status::STATUS_DISABLED);
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
                /** TODO: CHECK WHY NOT SAVED BUNDLE PRODUCT (PROBLEM WITH SKU) */
                $this->productRepository->save($productForDelete);
            } catch (CouldNotSaveException $e) {
                var_dump($e);
                /** TODO EXCEPTION */
            } catch (InputException $e) {
                var_dump($e);
                /** TODO EXCEPTION */
            } catch (StateException $e) {
                var_dump($e);
                /** TODO EXCEPTION */
            }
        }
    }

}
