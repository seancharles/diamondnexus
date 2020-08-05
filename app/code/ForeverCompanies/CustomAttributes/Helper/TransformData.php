<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace ForeverCompanies\CustomAttributes\Helper;

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
     * @var LinkInterfaceFactory
     */
    protected $linkFactory;

    /**
     * @var string[]
     */
    private $mappingProductType = [
        'Migration_Bracelets' => 'Bracelet',
        'Migration_Chains' => 'Chain',
        'Migration_Custom Cut' => 'Other',
        'Migration_Default' => 'Other',
        'Migration_Earrings' => 'Earring',
        'Migration_Gift Card' => 'Gift Card',
        'Migration_Loose Diamonds' => 'Diamond',
        'Migration_Loose Stones' => 'Stone',
        'Migration_Matched Sets' => 'Matched Set',
        'Migration_Matching Bands' => 'Matching Band',
        'Migration_Mens Rings' => 'Ring',
        'Migration_Necklaces' => 'Necklace',
        'Migration_Pendants' => 'Pendant',
        'Migration_Pure Carbon Rings' => 'Ring',
        'Migration_Ring Settings' => 'Ring Setting',
        'Migration_Rings' => 'Ring',
        'Migration_Simple' => 'Other',
        'Migration_Watches' => 'Watch'
    ];

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
     */
    public function __construct(
        Context $context,
        Config $config,
        CollectionFactory $collectionFactory,
        ProductRepository $productRepository,
        AttributeSetRepositoryInterface $attributeSetRepository,
        ExternalVideoEntryConverter $videoEntryConverter,
        LinkInterfaceFactory $linkFactory,
        OptionInterfaceFactory $optionInterfaceFactory
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
        $entityId = 135081; //Need delete it after testing

        $product = $this->productRepository->getById($entityId);
        if ($product->getTypeId() == Configurable::TYPE_CODE) {
            if (strpos($product->getName(), 'Chelsa') === false) {
                $this->convertConfigToBundle($product);
            }
        }
        $attributeSetName = $this->attrSetRepository->get($product->getAttributeSetId())->getAttributeSetName();
        $product->setCustomAttribute('product_type', $this->attributeSetToProductType($attributeSetName));
        if ($product->getCustomAttribute('product_type') == 'Stone') {
            $product->setCustomAttribute('allow_in_bundles', 1);
        }
        foreach (['youtube', 'video_url'] as $link) {
            $videoUrl = $product->getCustomAttribute($link);
            if ($videoUrl != null) {
                $this->addVideoToProduct($videoUrl, $product);
            }
        }
        $returnable = $product->getCustomAttribute('returnable');
        if ($returnable != null) {
            $product->setCustomAttribute('is_returnable', $returnable);
        }
        $tcw = $product->getCustomAttribute('tcw');
        if ($tcw != null) {
            $product->setCustomAttribute('acw', $tcw);
        }
        /** TODO: check other attributes */

        /** TODO: Delete attributes from delete list */

        /** Finally! */
        try {
            //$this->productRepository->save($product); DONT NEED NOW
        } catch (InputException $inputException) {
            var_dump($inputException);exit; //delet it after testing
            throw $inputException;
        } catch (\Exception $e) {
            var_dump($e);exit; //delete it after testing too
            throw new StateException(__('Cannot save product.'));
        }
        exit('stop test');
    }

    /**
     * @param string $attributeSetName
     * @return string
     */
    protected function attributeSetToProductType(string $attributeSetName)
    {
        return $this->mappingProductType[$attributeSetName];
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
        $videoData =  [
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
     */
    protected function convertConfigToBundle(Product $product)
    {
        /** @var Configurable $configurableInstance */
        $bundleOptions = $this->optionInterfaceFactory->create();
        /** @var ProductExtension $extensionAttributes */
        $extensionAttributes = $product->getExtensionAttributes();
        $productLinks = $product->getExtensionAttributes()->getConfigurableProductLinks() ?: [];
        /** TODO: Get configurable options and set it like title in bundle's options $links */
        $links = [];
        foreach ($productLinks as $productLinkId) {
            $linkedProduct = $this->productRepository->getById($productLinkId);
            $link = $this->linkFactory->create();
            $link->setSku($linkedProduct->getSku());
            $link->setQty(1);
            $link->setIsDefault(false);
            $link->setPrice($linkedProduct->getPrice());
            $link->setPriceType(\Magento\Bundle\Api\Data\LinkInterface::PRICE_TYPE_FIXED);
            $links[] = $link;
            /** TODO: Delete parent_entity_id and save it */
        }
        $bundleOptions->setTitle('Here is title'); // TODO: Set here configurable option label
        $bundleOptions->setType('radio'); // TODO: not only radio
        $bundleOptions->setRequired(true);
        $bundleOptions->setProductLinks($links);
        $extensionAttributes->setBundleProductOptions([$bundleOptions]);
        $product->setExtensionAttributes($extensionAttributes);
        $product->setTypeId('bundle');
    }

}
