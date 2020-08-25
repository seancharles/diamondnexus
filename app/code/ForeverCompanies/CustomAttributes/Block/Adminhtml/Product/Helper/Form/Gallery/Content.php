<?php

namespace ForeverCompanies\CustomAttributes\Block\Adminhtml\Product\Helper\Form\Gallery;

use Cloudinary\Cloudinary\Helper\MediaLibraryHelper;
use Cloudinary\Cloudinary\Model\ProductSpinsetMapFactory;
use ForeverCompanies\CustomAttributes\Helper\Media;
use Magento\Backend\Block\Template\Context;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Media\Config;
use Magento\Catalog\Model\Product\Option;
use Magento\Framework\Json\DecoderInterface;
use Magento\Framework\Json\EncoderInterface;

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
        array $data = []
    )
    {
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
            return [];
        }
        return $this->_jsonEncoder->encode($images);
    }

    /**
     * @return array
     */
    public function getProductOptionTypes()
    {
        $optionTypes = [];
        /** @var Product $product */
        $product = $this->getElement()->getDataObject();
        /** @var Option $option */
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
        return $optionTypes;
    }

    public function getProductBundleSelections()
    {
        $data = [];
        /** @var Product $product */
        $product = $this->getElement()->getDataObject();
        if ($product->getTypeId() == \Magento\Catalog\Model\Product\Type::TYPE_BUNDLE) {
            $options = $product->getExtensionAttributes()->getBundleProductOptions();
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
}
