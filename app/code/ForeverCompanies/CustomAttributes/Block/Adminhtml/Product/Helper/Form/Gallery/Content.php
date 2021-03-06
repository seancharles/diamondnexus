<?php

namespace ForeverCompanies\CustomAttributes\Block\Adminhtml\Product\Helper\Form\Gallery;

use Cloudinary\Cloudinary\Helper\MediaLibraryHelper;
use Cloudinary\Cloudinary\Model\ProductSpinsetMapFactory;
use ForeverCompanies\CustomAttributes\Helper\Media;
use Magento\Backend\Block\Template\Context;
use Magento\Catalog\Api\Data\ProductExtension;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Media\Config;
use Magento\Catalog\Model\Product\Option;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Json\DecoderInterface;
use Magento\Framework\Json\EncoderInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use \ForeverCompanies\LinkProduct\Model\Accessory as LinkedProduct;

/**
 * Block for gallery content.
 */
class Content extends \Cloudinary\Cloudinary\Block\Adminhtml\Product\Helper\Form\Gallery\Content
{
    /**
     * @var Media
     */
    protected $mediaHelper;

    /**
     * @var Json
     */
    protected $jsonHelper;
    
    /**
     * @var Magento\Catalog\Api\ProductLinkRepositoryInterface
     */
    protected $productLinkRepository;
    
    /**
     * @var \Magento\Catalog\Model\Product\Option
     */
    protected $configurableOption;
    
    /**
     * @var \Magento\Catalog\Model\Product\Option
     */
    protected $customOption;

    /**
     * @var string
     */
    protected $_template = self::TEMPLATE_GALLERY_PHTML;

    const TEMPLATE_GALLERY_PHTML = 'ForeverCompanies_CustomAttributes::catalog/product/helper/gallery.phtml';

    /**
     * Content constructor.
     * @param Context $context
     * @param EncoderInterface $jsonEncoder
     * @param DecoderInterface $jsonDecoder
     * @param Config $mediaConfig
     * @param MediaLibraryHelper $mediaLibraryHelper
     * @param ProductSpinsetMapFactory $productSpinsetMapFactory
     * @param Media $mediaHelper
     * @param Json $jsonHelper
     * @param array $data
     */
    public function __construct(
        Context $context,
        EncoderInterface $jsonEncoder,
        DecoderInterface $jsonDecoder,
        Config $mediaConfig,
        MediaLibraryHelper $mediaLibraryHelper,
        ProductSpinsetMapFactory $productSpinsetMapFactory,
        Media $mediaHelper,
        Json $jsonHelper,
        Configurable $configurableOption,
        Option $customOption,
        LinkedProduct $linkedProduct,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $jsonEncoder,
            $jsonDecoder,
            $mediaConfig,
            $mediaLibraryHelper,
            $productSpinsetMapFactory,
            $data
        );
        $this->mediaHelper = $mediaHelper;
        $this->jsonHelper = $jsonHelper;
        $this->configurableOption = $configurableOption;
        $this->customOption = $customOption;
        $this->linkedProduct = $linkedProduct;
    }

    /**
     * @return array|string
     */
    public function getImagesJson()
    {
        $images = $this->_jsonDecoder->decode(parent::getImagesJson());
        if ($images) {
            $images = $this->mediaHelper->addFieldsToMedia($images);
        } else {
            return "[]";
        }
        return $this->_jsonEncoder->encode($images);
    }

    /**
     * @param $data
     * @return bool|string
     */
    public function jsonSerialize($data)
    {
        return $this->jsonHelper->serialize($data);
    }

    /**
     * @return array
     */
    public function getProductOptionTypes()
    {
        $optionTypes = [];
        /** @var Product $product */
        $product = $this->getData('element')->getDataObject();
        /** @var Option $option */
        if ($product->getId()) {
            foreach ($product->getOptions() as $option) {
                if ($option->getTitle() == 'Precious Metal') {
                    foreach ($option->getValues() as $value) {
                        $optionTypes[] = [
                            'id' => $value->getOptionTypeId(),
                            'label' => $value->getTitle()
                        ];
                    }
                }
            }
        }
        return $optionTypes;
    }

    /**
     * @return array
     */
    public function getUiRoles()
    {
        return $this->mediaHelper::CUSTOM_UI_ROLES;
    }

    /**
     * @return array
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getProductBundleSelections()
    {
        $data = [];
        /** @var Product $product */
        $product = $this->getData('element')->getDataObject();
        if ($product->getTypeId() == \Magento\Catalog\Model\Product\Type::TYPE_BUNDLE && $product->getId()) {
            /** @var ProductExtension $extensionAttributes */
            $extensionAttributes = $product->getExtensionAttributes();
            $options = $extensionAttributes->getBundleProductOptions();
            foreach ($options as $option) {
                if ($option->getTitle() == 'Center Stone Size') {
                    $data = $this->mediaHelper->prepareBundleSelectionsFromLinks($option->getProductLinks());
                }
            }
            if ($data == null) {
                $data = [];
            }
        }
        return $data;
    }
    
    public function getLinkedProducts()
    {
        $values = [];
        /** @var Product $product */
        $product = $this->getData('element')->getDataObject();
        
        $linkedProducts = $this->linkedProduct->getAccessoryProducts($product);
        
        foreach($linkedProducts as $product) {
            $values[] = [
                'id' => $product->getId(),
                'label' => $product->getName()
            ];
        }
        
        return $values;
    }
    
    public function getMetalTypes()
    {
        $values = [];
        /** @var Product $product */
        $product = $this->getData('element')->getDataObject();
        
        $productAttributeOptions = $this->configurableOption->getConfigurableAttributesAsArray($product);
        
        foreach ($productAttributeOptions as $option) {
            if($option['label'] == "Precious Metal") {
                foreach($option['values'] as $value) {
                    $values[] = [
                        'id' => $value['value_index'],
                        'label' => $value['label']
                    ];
                }
            }
        }
        
        return $values;
    }
}
