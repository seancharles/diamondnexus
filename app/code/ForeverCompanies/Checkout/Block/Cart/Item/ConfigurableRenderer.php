<?php

namespace ForeverCompanies\Checkout\Block\Cart\Item;

use Magento\Catalog\Pricing\Price\ConfiguredPriceInterface;
use Magento\Checkout\Block\Cart\Item\Renderer\Actions;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\View\Element\AbstractBlock;
use Magento\Framework\View\Element\Message\InterpretationStrategyInterface;
use Magento\Quote\Model\Quote\Item\AbstractItem;
use Magento\Framework\App\ObjectManager;
use Magento\Catalog\Model\Product\Configuration\Item\ItemResolverInterface;
use Magento\Quote\Model\ResourceModel\Quote\Item\CollectionFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product\Attribute\Repository;
use Magento\Catalog\Helper\Image;

class ConfigurableRenderer extends \Magento\ConfigurableProduct\Block\Cart\Item\Renderer\Configurable
{
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    /**
     * @var AbstractItem
     */
    protected $_item;

    /**
     * @var string
     */
    protected $_productUrl;

    /**
     * Whether qty will be converted to number
     *
     * @var bool
     */
    protected $_strictQtyMode = true;

    /**
     * Check, whether product URL rendering should be ignored
     *
     * @var bool
     */
    protected $_ignoreProductUrl = false;

    /**
     * Catalog product configuration
     *
     * @var \Magento\Catalog\Helper\Product\Configuration
     */
    protected $_productConfig = null;

    /**
     * @var \Magento\Framework\Url\Helper\Data
     */
    protected $_urlHelper;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * @var \Magento\Catalog\Block\Product\ImageBuilder
     */
    protected $imageBuilder;

    /**
     * @var PriceCurrencyInterface
     */
    protected $priceCurrency;

    /**
     * @var \Magento\Framework\Module\Manager
     */
    public $moduleManager;

    /**
     * @var InterpretationStrategyInterface
     */
    private $messageInterpretationStrategy;

    /** @var ItemResolverInterface */
    private $itemResolver;

    protected $priceHelper;
    protected $itemCollection;
    protected $productRepository;
    protected $attributeRepository;
    protected $helperImage;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Catalog\Helper\Product\Configuration $productConfig,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Catalog\Block\Product\ImageBuilder $imageBuilder,
        \Magento\Framework\Url\Helper\Data $urlHelper,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        PriceCurrencyInterface $priceCurrency,
        \Magento\Framework\Module\Manager $moduleManager,
        InterpretationStrategyInterface $messageInterpretationStrategy,
        array $data = [],
        ItemResolverInterface $itemResolver = null,
        PriceCurrencyInterface $priceHelper,
        CollectionFactory $itemCollection,
        ProductRepositoryInterface $productRepository,
        Repository $attributeRepository,
        Image $helperImage

    ) {
        $this->priceCurrency = $priceCurrency;
        $this->imageBuilder = $imageBuilder;
        $this->_urlHelper = $urlHelper;
        $this->_productConfig = $productConfig;
        $this->_checkoutSession = $checkoutSession;
        $this->messageManager = $messageManager;
        parent::__construct(
            $context, $productConfig, $checkoutSession, $imageBuilder, $urlHelper, $messageManager, $priceCurrency,
            $moduleManager, $messageInterpretationStrategy, $data
        );
        $this->_isScopePrivate = true;
        $this->moduleManager = $moduleManager;
        $this->messageInterpretationStrategy = $messageInterpretationStrategy;
        $this->itemResolver = $itemResolver ?: ObjectManager::getInstance()->get(ItemResolverInterface::class);
        $this->priceHelper = $priceHelper;
        $this->itemCollection = $itemCollection;
        $this->productRepository = $productRepository;
        $this->attributeRepository = $attributeRepository;
        $this->helperImage = $helperImage;
    }

    public function getTaggedImage()
    {
        $images = [];
        $metalMap = [];

        $configOptions = $this->getItem()->getBuyRequest()->getSuperAttribute();
        $product = $this->productRepository->getById($this->getItem()->getProductId());
        $imageGallery = $product->getMediaGalleryImages();

        if (isset($configOptions[145]) === true) {
            $metalOptions = $this->attributeRepository->get('metal_type')->getOptions();

            foreach ($metalOptions as $metalOption) {
                $metalMap[$metalOption->getValue()] = strtolower($metalOption->getLabel());
            }

            foreach ($imageGallery as $image) {
                $label = strtolower($image->getLabel());

                if (strlen($label) > 0) {
                    if (strpos($label, "default") !== false) {
                        $metalType = $metalMap[$configOptions[145]];

                        if (strpos($label, $metalType) !== false) {
                            $images[] = $image->getUrl();
                        }
                    }
                }
            }
        }

        // used as a default in case a tagged image isn't found
        foreach ($imageGallery as $image) {
            $images[] = $image->getUrl();
            break;
        }

        // handling for no images found in gallery
        if (count($images) == 0) {
            $images[] = $this->helperImage->getDefaultPlaceholderUrl('thumbnail');
        }

        return '<img
            class="product-image-photo cloudinary-lazyload-processed"
            src="' . $this->formatCloudinaryImagePath($images[0], 100) . '"
            data-original="' . $this->formatCloudinaryImagePath($images[0], 100) . '"
            width="165" height="165" alt="' . $this->getItem()->getName() . '"
            style="display: block;" 
        />';
    }

    protected function formatCloudinaryImagePath($path = null, $width = 0, $quality = 90)
    {
        $host = 'https://res-2.cloudinary.com/foco/image/upload/w_300,c_scale/q_auto,f_auto/';

        // image/upload/media/catalog/product/t/i/tiffany-style-round-whiteview-1.jpg
        if (strpos($path, $host) === false) {
            $folderPosition = strpos($path, "media/catalog/product");

            // get the actual path to the file from uri
            $file = substr($path, $folderPosition);

            return $host . $file;
        } else {
            return $path;
        }
    }

    public function getPriceHelper()
    {
        return $this->priceCurrency;
    }

    public function getItemsCollection()
    {
        return $this->itemCollection->create();
    }
}