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
use Magento\Catalog\Api\AttributeSetRepositoryInterface;
use Magento\Catalog\Api\Data\ProductExtension;
use Magento\Catalog\Model\Product;
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
     * @var LinkInterfaceFactory
     */
    protected $linkFactory;

    /**
     * @var ExternalVideoEntryConverter
     */
    private $externalVideoEntryConverter;

    /**
     * @var OptionInterfaceFactory
     */
    private $optionInterfaceFactory;

    /**
     * @param Context $context
     * @param Config $config
     * @param CollectionFactory $collectionFactory
     * @param ProductRepository $productRepository
     * @param AttributeSetRepositoryInterface $attributeSetRepository
     * @param ExternalVideoEntryConverter $videoEntryConverter
     * @param LinkInterfaceFactory $linkFactory
     * @param OptionInterfaceFactory $optionInterfaceFactory
     * @param Mapping $mapping
     */
    public function __construct(
        Context $context,
        Config $config,
        CollectionFactory $collectionFactory,
        ProductRepository $productRepository,
        AttributeSetRepositoryInterface $attributeSetRepository,
        ExternalVideoEntryConverter $videoEntryConverter,
        LinkInterfaceFactory $linkFactory,
        OptionInterfaceFactory $optionInterfaceFactory,
        Mapping $mapping
    )
    {
        parent::__construct($context);
        $this->eav = $config;
        $this->productCollectionFactory = $collectionFactory;
        $this->productRepository = $productRepository;
        $this->attrSetRepository = $attributeSetRepository;
        $this->externalVideoEntryConverter = $videoEntryConverter;
        $this->linkFactory = $linkFactory;
        $this->optionInterfaceFactory = $optionInterfaceFactory;
        $this->mapping = $mapping;
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
        $entityId = 9403; //Need delete it after testing

        $product = $this->productRepository->getById($entityId);
        if ($product->getTypeId() == Configurable::TYPE_CODE) {
            if (strpos($product->getName(), 'Chelsa') === false) {
                $this->convertConfigToBundle($product);
            }
        }
        $attributeSetName = $this->attrSetRepository->get($product->getAttributeSetId())->getAttributeSetName();
        $productType = $this->mapping->attributeSetToProductType($attributeSetName);
        $product->setCustomAttribute('product_type', $productType);
        if ($product->getCustomAttribute('product_type') == 'Stone') {
            $product->setCustomAttribute('allow_in_bundles', 1);
        }
        foreach (['youtube', 'video_url'] as $link) {
            $videoUrl = $product->getCustomAttribute($link);
            if ($videoUrl != null) {
                $this->addVideoToProduct($videoUrl, $product);
            }
        }
        foreach(['returnable'=>'is_returnable', 'tcw'=>'acw'] as $before => $new)
        {
            $customAttribute = $product->getCustomAttribute($before);
            if ($customAttribute != null) {
                $product->setCustomAttribute($new, $customAttribute);
            }
        }

        /** Finally! */
        try {
            //$this->productRepository->save($product); TESTING
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
        /** @var ProductExtension $extensionAttributes */
        $extensionAttributes = $product->getExtensionAttributes();
        $productLinks = $product->getExtensionAttributes()->getConfigurableProductLinks() ?: [];
        $productOptions = $product->getExtensionAttributes()->getConfigurableProductOptions() ?: [];
        $links = [];
        $this->transformOptionsToBundle($product, $productOptions);
        foreach ($productLinks as $productLinkId) {
            $linkedProduct = $this->productRepository->getById($productLinkId);
            $links[] = $this->createNewLink($linkedProduct);
            $this->changeLinkedProduct($linkedProduct, $productOptionData);
        }
        /*$bundleOptions->setTitle($productOptionData['label']);
        $bundleOptions->setType('select');
        $bundleOptions->setRequired(true);
        $bundleOptions->setProductLinks($links);*/
        $extensionAttributes->setBundleProductOptions([$bundleOptions]);
        $product->setExtensionAttributes($extensionAttributes);
        $product->setTypeId('bundle');
    }

    /**
     * @param Product $product
     * @param array $productOptions
     */
    private function transformOptionsToBundle(Product $product, array $productOptions)
    {
        $optionsData = $this->mapping->prepareOptionsForBundle($product, $productOptions);
        $product->setBundleOptionsData($optionsData['bundle']);
        $options = [];
        foreach ($product->getBundleOptionsData() as $key => $optionData) {
            $option = $this->optionInterfaceFactory->create();
            /** TODO: set data from $optionsData['options'] */

            $options[] = $option;
        }
        $extension = $product->getExtensionAttributes();
        $extension->setBundleProductOptions($options);
        $product->setExtensionAttributes($extension);
    }

    /**
     * @param Product $linkedProduct
     * @return LinkInterface
     */
    private function createNewLink(Product $linkedProduct)
    {
        $link = $this->linkFactory->create();
        /** TODO: REFACTORING FOR STONE!!! */
        $link->setSku($linkedProduct->getSku());
        $link->setQty(1);
        $link->setIsDefault(false);
        $link->setPrice($linkedProduct->getPrice());
        $link->setPriceType(LinkInterface::PRICE_TYPE_FIXED);
        return $link;
    }

    /**
     * @param Product $linkedProduct
     * @param array $productOptionData
     * TODO: REFACTORING!!!!
     */
    private function changeLinkedProduct(Product $linkedProduct, array $productOptionData)
    {
        $optionAttributeCode = $productOptionData['product_attribute']->getAttributeCode();
        $options = $productOptionData['options'];
        $attributeValue = $linkedProduct->getData($optionAttributeCode);
        foreach ($options as $option) {
            if ($attributeValue == $option['value_index']) {
                if ($linkedProduct->getData('old_name') === null) {
                    $linkedProduct->setData('old_name', $linkedProduct->getName());
                }
                $linkedProduct->setName($option['label']);
                $linkedProduct->setVisibility(false);
                $movedAsPart = 'Moved as part ' . $productOptionData['product_id'];
                $devTag = $linkedProduct->getData('dev_tag');
                if ($devTag !== null && strpos($devTag, $movedAsPart) === false) {
                    $linkedProduct->setData('dev_tag', $devTag . ', ' . $movedAsPart);
                }
                if ($devTag === null) {
                    $linkedProduct->setData('dev_tag', $movedAsPart);
                }

                try {
                    //$this->productRepository->save($linkedProduct); TESTING
                } catch (CouldNotSaveException $e) {
                    /** TODO EXCEPTION */
                } catch (InputException $e) {
                    /** TODO EXCEPTION */
                } catch (StateException $e) {
                    /** TODO EXCEPTION */
                }
                break;
            }
        }
    }

}
