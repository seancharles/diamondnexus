<?php

namespace ForeverCompanies\DynamicBundle\Helper\Catalog\Product;
 
use Magento\Catalog\Helper\Product\Configuration\ConfigurationInterface;
use Magento\Catalog\Model\Product\Configuration\Item\ItemInterface;
use Magento\Framework\App\Helper\AbstractHelper;
 
class Configuration extends \Magento\Bundle\Helper\Catalog\Product\Configuration
{
    /**
     * Core data
     *
     * @var \Magento\Framework\Pricing\Helper\Data
     */
    protected $pricingHelper;

    /**
     * Catalog product configuration
     *
     * @var \Magento\Catalog\Helper\Product\Configuration
     */
    protected $productConfiguration;

    /**
     * Escaper
     *
     * @var \Magento\Framework\Escaper
     */
    protected $escaper;

    /**
     * Serializer interface instance.
     *
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    private $serializer;
	
	protected $shipperLogger;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Catalog\Helper\Product\Configuration $productConfiguration
     * @param \Magento\Framework\Pricing\Helper\Data $pricingHelper
     * @param \Magento\Framework\Escaper $escaper
     * @param \Magento\Framework\Serialize\Serializer\Json|null $serializer
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Catalog\Helper\Product\Configuration $productConfiguration,
        \Magento\Framework\Pricing\Helper\Data $pricingHelper,
        \Magento\Framework\Escaper $escaper,
        \Magento\Framework\Serialize\Serializer\Json $serializer = null,
		\ShipperHQ\Shipper\Helper\LogAssist $shipperLogger
    ) {
        $this->productConfiguration = $productConfiguration;
        $this->pricingHelper = $pricingHelper;
        $this->escaper = $escaper;
        $this->serializer = $serializer ?: \Magento\Framework\App\ObjectManager::getInstance()
            ->get(\Magento\Framework\Serialize\Serializer\Json::class);
		$this->shipperLogger = $shipperLogger;
    }
	
    /**
     * Get bundled selections (slections-products collection)
     *
     * Returns array of options objects.
     * Each option object will contain array of selections objects
     *
     * @param ItemInterface $item
     * @return array
     */
    public function getBundleOptions(ItemInterface $item)
    {
        $options = [];
        $product = $item->getProduct();

        /** @var \Magento\Bundle\Model\Product\Type $typeInstance */
        $typeInstance = $product->getTypeInstance();

		// get bundle options
		$optionsQuoteItemOption = $item->getOptionByCode('bundle_option_ids');
		$bundleOptionsIds = $optionsQuoteItemOption
			? $this->serializer->unserialize($optionsQuoteItemOption->getValue())
			: [];

		// fetch dynamic bundled item
		$bundledItemId = $item->getBuyRequest()->getDynamicBundledItemId();
		
		// fetch bundle child custom options
		$bundleChildCustomOptions = $item->getBuyRequest()->getOptions();

		// specific for 1216 engagement rings
		if($bundledItemId){
			
			$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
			$dynamicProduct = $objectManager->create('Magento\Catalog\Model\Product')->load($bundledItemId);
		}

		if ($bundleOptionsIds) {
			/** @var \Magento\Bundle\Model\ResourceModel\Option\Collection $optionsCollection */
			$optionsCollection = $typeInstance->getOptionsByIds($bundleOptionsIds, $product);

			// get and add bundle selections collection
			$selectionsQuoteItemOption = $item->getOptionByCode('bundle_selection_ids');

			$bundleSelectionIds = $this->serializer->unserialize($selectionsQuoteItemOption->getValue());

			if (!empty($bundleSelectionIds)) {
				$selectionsCollection = $typeInstance->getSelectionsByIds($bundleSelectionIds, $product);

				$bundleOptions = $optionsCollection->appendSelections($selectionsCollection, true);
				foreach ($bundleOptions as $bundleOption) {

					// determine if option is dynamic
					if($bundleOption->getIsDynamicSelection() == 1) {
						
						if($bundledItemId > 0) {
							
							$itemPrice = $this->pricingHelper->currency(($dynamicProduct->getBundlePrice() > 0) ? $dynamicProduct->getBundlePrice(): $dynamicProduct->getPrice());
							
							$options = array([
									'label' => $bundleOption->getTitle(),
									'value' => [$dynamicProduct->getName() . " " . $itemPrice]]
							);
						}
						
					} else {
						
						// handle standard options
						if ($bundleOption->getSelections()) {
							$option = ['label' => $bundleOption->getTitle(), 'value' => []];

							$bundleSelections = $bundleOption->getSelections();

							foreach ($bundleSelections as $bundleSelection) {
								$qty = $this->getSelectionQty($product, $bundleSelection->getSelectionId()) * 1;
								if ($qty) {
									
									$itemPrice = $this->getChildCustomOptionPrice($product, $bundleChildCustomOptions);
									
									$option['value'][] = $qty . ' x '
										. $this->escaper->escapeHtml($bundleSelection->getName())
										. ' '
										. $itemPrice;
										
									$option['has_html'] = true;
								}
							}

							if ($option['value']) {
								$options[] = $option;
							}
						}
					}
				}
			}
		}

        return $options;
    }
	
	public function getChildCustomOptionPrice($product, $options)
	{
		$customOptionPrice = 0;
		
			$this->shipperLogger->postDebug(
				'DynamicBundle_Configuration',
				'options',
				$options
			);
		
		foreach($product->getOptions() as $option)
		{
			$values = $option->getValues();
			
			foreach($values as $value)
			{
				if( isset($options[$option->getId()]) == true && $options[$option->getId()] == $value->getId() && $value->getPrice() > 0 )
				{
					$customOptionPrice += $value->getPrice();
					
					break;
				}
			}
		}
		
		$price = (($product->getBundlePrice() > 0) ? $product->getBundlePrice(): $product->getPrice());
		
		return $this->pricingHelper->currency($price + $customOptionPrice);
	}
}