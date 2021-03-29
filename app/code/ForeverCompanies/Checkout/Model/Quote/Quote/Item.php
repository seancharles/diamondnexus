<?php
namespace ForeverCompanies\Checkout\Model\Quote\Quote;

use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Quote\Api\Data\CartItemInterface;

/**
 * Sales Quote Item Model
 *
 * @method \Magento\Quote\Model\ResourceModel\Quote\Item _getResource()
 * @method \Magento\Quote\Model\ResourceModel\Quote\Item getResource()
 * @method string getCreatedAt()
 * @method \Magento\Quote\Model\Quote\Item setCreatedAt(string $value)
 * @method string getUpdatedAt()
 * @method \Magento\Quote\Model\Quote\Item setUpdatedAt(string $value)
 * @method int getStoreId()
 * @method \Magento\Quote\Model\Quote\Item setStoreId(int $value)
 * @method int getParentItemId()
 * @method \Magento\Quote\Model\Quote\Item setParentItemId(int $value)
 * @method int getIsVirtual()
 * @method \Magento\Quote\Model\Quote\Item setIsVirtual(int $value)
 * @method string getDescription()
 * @method \Magento\Quote\Model\Quote\Item setDescription(string $value)
 * @method string getAdditionalData()
 * @method \Magento\Quote\Model\Quote\Item setAdditionalData(string $value)
 * @method int getFreeShipping()
 * @method \Magento\Quote\Model\Quote\Item setFreeShipping(int $value)
 * @method int getIsQtyDecimal()
 * @method \Magento\Quote\Model\Quote\Item setIsQtyDecimal(int $value)
 * @method int getNoDiscount()
 * @method \Magento\Quote\Model\Quote\Item setNoDiscount(int $value)
 * @method float getWeight()
 * @method \Magento\Quote\Model\Quote\Item setWeight(float $value)
 * @method float getBasePrice()
 * @method \Magento\Quote\Model\Quote\Item setBasePrice(float $value)
 * @method float getCustomPrice()
 * @method float getTaxPercent()
 * @method \Magento\Quote\Model\Quote\Item setTaxPercent(float $value)
 * @method \Magento\Quote\Model\Quote\Item setTaxAmount(float $value)
 * @method \Magento\Quote\Model\Quote\Item setBaseTaxAmount(float $value)
 * @method \Magento\Quote\Model\Quote\Item setRowTotal(float $value)
 * @method \Magento\Quote\Model\Quote\Item setBaseRowTotal(float $value)
 * @method float getRowTotalWithDiscount()
 * @method \Magento\Quote\Model\Quote\Item setRowTotalWithDiscount(float $value)
 * @method float getRowWeight()
 * @method \Magento\Quote\Model\Quote\Item setRowWeight(float $value)
 * @method float getBaseTaxBeforeDiscount()
 * @method \Magento\Quote\Model\Quote\Item setBaseTaxBeforeDiscount(float $value)
 * @method float getTaxBeforeDiscount()
 * @method \Magento\Quote\Model\Quote\Item setTaxBeforeDiscount(float $value)
 * @method float getOriginalCustomPrice()
 * @method \Magento\Quote\Model\Quote\Item setOriginalCustomPrice(float $value)
 * @method string getRedirectUrl()
 * @method \Magento\Quote\Model\Quote\Item setRedirectUrl(string $value)
 * @method float getBaseCost()
 * @method \Magento\Quote\Model\Quote\Item setBaseCost(float $value)
 * @method \Magento\Quote\Model\Quote\Item setPriceInclTax(float $value)
 * @method float getBasePriceInclTax()
 * @method \Magento\Quote\Model\Quote\Item setBasePriceInclTax(float $value)
 * @method \Magento\Quote\Model\Quote\Item setRowTotalInclTax(float $value)
 * @method float getBaseRowTotalInclTax()
 * @method \Magento\Quote\Model\Quote\Item setBaseRowTotalInclTax(float $value)
 * @method int getGiftMessageId()
 * @method \Magento\Quote\Model\Quote\Item setGiftMessageId(int $value)
 * @method string getWeeeTaxApplied()
 * @method \Magento\Quote\Model\Quote\Item setWeeeTaxApplied(string $value)
 * @method float getWeeeTaxAppliedAmount()
 * @method \Magento\Quote\Model\Quote\Item setWeeeTaxAppliedAmount(float $value)
 * @method float getWeeeTaxAppliedRowAmount()
 * @method \Magento\Quote\Model\Quote\Item setWeeeTaxAppliedRowAmount(float $value)
 * @method float getBaseWeeeTaxAppliedAmount()
 * @method \Magento\Quote\Model\Quote\Item setBaseWeeeTaxAppliedAmount(float $value)
 * @method float getBaseWeeeTaxAppliedRowAmnt()
 * @method \Magento\Quote\Model\Quote\Item setBaseWeeeTaxAppliedRowAmnt(float $value)
 * @method float getWeeeTaxDisposition()
 * @method \Magento\Quote\Model\Quote\Item setWeeeTaxDisposition(float $value)
 * @method float getWeeeTaxRowDisposition()
 * @method \Magento\Quote\Model\Quote\Item setWeeeTaxRowDisposition(float $value)
 * @method float getBaseWeeeTaxDisposition()
 * @method \Magento\Quote\Model\Quote\Item setBaseWeeeTaxDisposition(float $value)
 * @method float getBaseWeeeTaxRowDisposition()
 * @method \Magento\Quote\Model\Quote\Item setBaseWeeeTaxRowDisposition(float $value)
 * @method float getDiscountTaxCompensationAmount()
 * @method \Magento\Quote\Model\Quote\Item setDiscountTaxCompensationAmount(float $value)
 * @method float getBaseDiscountTaxCompensationAmount()
 * @method \Magento\Quote\Model\Quote\Item setBaseDiscountTaxCompensationAmount(float $value)
 * @method null|bool getHasConfigurationUnavailableError()
 * @method \Magento\Quote\Model\Quote\Item setHasConfigurationUnavailableError(bool $value)
 * @method \Magento\Quote\Model\Quote\Item unsHasConfigurationUnavailableError()
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class Item extends \Magento\Quote\Model\Quote\Item
{
    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'sales_quote_item';

    /**
     * Parameter name in event
     *
     * In observe method you can use $observer->getEvent()->getObject() in this case
     *
     * @var string
     */
    protected $_eventObject = 'item';

    /**
     * Quote model object
     *
     * @var \Magento\Quote\Model\Quote
     */
    protected $_quote;

    /**
     * Item options array
     *
     * @var array
     */
    protected $_options = [];

    /**
     * Item options by code cache
     *
     * @var array
     */
    protected $_optionsByCode = [];

    /**
     * Not Represent options
     *
     * @var array
     */
    protected $_notRepresentOptions = ['info_buyRequest'];

    /**
     * Flag stating that options were successfully saved
     *
     */
    protected $_flagOptionsSaved;

    /**
     * Array of errors associated with this quote item
     *
     * @var \Magento\Sales\Model\Status\ListStatus
     */
    protected $_errorInfos;

    /**
     * @var \Magento\Framework\Locale\FormatInterface
     */
    protected $_localeFormat;

    /**
     * @var \Magento\Quote\Model\Quote\Item\OptionFactory
     */
    protected $_itemOptionFactory;

    /**
     * @var \Magento\Quote\Model\Quote\Item\Compare
     */
    protected $quoteItemCompare;

    /**
     * @var \Magento\CatalogInventory\Api\StockRegistryInterface
     */
    protected $stockRegistry;

    protected $scopeConfig;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param ExtensionAttributesFactory $extensionFactory
     * @param AttributeValueFactory $customAttributeFactory
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
     * @param \Magento\Sales\Model\Status\ListFactory $statusListFactory
     * @param \Magento\Framework\Locale\FormatInterface $localeFormat
     * @param Item\OptionFactory $itemOptionFactory
     * @param Item\Compare $quoteItemCompare
     * @param \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        ExtensionAttributesFactory $extensionFactory,
        AttributeValueFactory $customAttributeFactory,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        \Magento\Sales\Model\Status\ListFactory $statusListFactory,
        \Magento\Framework\Locale\FormatInterface $localeFormat,
        \Magento\Quote\Model\Quote\Item\OptionFactory $itemOptionFactory,
        \Magento\Quote\Model\Quote\Item\Compare $quoteItemCompare,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->scopeConfig = $scopeConfig;
        // $this->registry = $registry;
        // $this->extensionFactory = $extensionFactory;
        // $this->customAttributeFactory = $customAttributeFactory;
        // $this->productRepository = $productRepository;
        // $this->priceCurrency = $priceCurrency;
        $this->_errorInfos = $statusListFactory->create();
        $this->_localeFormat = $localeFormat;
        $this->_itemOptionFactory = $itemOptionFactory;
        $this->quoteItemCompare = $quoteItemCompare;
        $this->stockRegistry = $stockRegistry;
        $this->resource = $resource;
        $this->resourceCollection = $resourceCollection;
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $productRepository,
            $priceCurrency,
            $statusListFactory,
            $localeFormat,
            $itemOptionFactory,
            $quoteItemCompare,
            $stockRegistry,
            $resource,
            $resourceCollection,
            $data
        );
    }

    public function representProduct($product)
    {
        $itemProduct = $this->getProduct();

        if (!$product || $itemProduct->getId() == $product->getId()) {
            return true;
        }

        if (!$product || $itemProduct->getId() != $product->getId()) {
            return false;
        }

        /**
         * Check maybe product is planned to be a child of some quote item - in this case we limit search
         * only within same parent item
         */
        $stickWithinParent = $product->getStickWithinParent();
        if ($stickWithinParent) {
            if ($this->getParentItem() !== $stickWithinParent) {
                return false;
            }
        }

        // Check options
        $itemOptions = $this->getOptionsByCode();
        $productOptions = $product->getCustomOptions();

        if (!$this->compareOptions($itemOptions, $productOptions)) {
            return false;
        }
        if (!$this->compareOptions($productOptions, $itemOptions)) {
            return false;
        }
    }
}