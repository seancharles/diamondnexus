<?php
namespace ForeverCompanies\DynamicBundle\Plugins\Shipperhq;

class ShipperMapper
{
    protected $shipperLogger;
	protected $productRepository;
	protected $itemoption;
	
	protected $aShipGroupDays = array(
		'0 Day' => 0,
		'1 Day' => 1,
		'2 Day' => 2,
		'3 Day' => 3,
		'4 Day' => 4,
		'5 Day' => 5,
		'6 Day' => 6,
		'7 Day' => 7,
		'8 Day' => 8,
		'9 Day' => 9,
		'10 Day' => 10,
		'11 Day' => 11,
		'12 Day' => 12,
		'13 Day' => 13,
		'14 Day' => 14,
		'15 Day' => 15,
		'16 Day' => 16,
		'17 Day' => 17,
		'18 Day' => 18,
		'19 Day' => 19,
		'20 Day' => 20,
		'21 Day' => 21
	);
	
	protected $aShippingGroupMap;
	
    public function __construct(
		\ShipperHQ\Shipper\Helper\LogAssist $shipperLogger,
		\Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
		\Magento\Catalog\Api\ProductCustomOptionRepositoryInterface $productOptionRepository,
		\Magento\Eav\Api\AttributeRepositoryInterface $eavAttributeRepository
	) {
		$this->shipperLogger = $shipperLogger;
		$this->productRepository = $productRepository;
		$this->productOptionRepository = $productOptionRepository;
		$this->eavAttributeRepository = $eavAttributeRepository;
    }

    public function afterGetFormattedItems(
        \ShipperHQ\Shipper\Model\Carrier\Processor\ShipperMapper $subject,
        array $result,
		$request,
		$magentoItems,
		$childItems = false
    ) {
		$this->getShippingGroupMap();

		$aShipGroup = array();
		
		foreach($magentoItems as $item)
		{
			$buyRequest = $item->getBuyRequest();

			$productShippingGroup = $item->getProduct()->getShipperhqShippingGroup();

			$aShipGroup[] = $this->getShippingGroupDays(
				$this->aShippingGroupMap[$productShippingGroup]
			);

			/*
				$this->shipperLogger->postDebug(
					'Plugin_ShipperMapper',
					'product attribute',
					$this->aShippingGroupMap[$productShippingGroup]
				);
			*/

			$options = $this->productOptionRepository->getProductOptions($item->getProduct());

			foreach($options as $option)
			{
				$values = $option->getValues();
				
				foreach($values as $value) {
					
					/*
						$this->shipperLogger->postDebug(
							'Plugin_ShipperMapper',
							'value',
							$value->getId()
						);
					*/
					
					if( $buyRequest['options'][$option['option_id']] == $value->getId() )
					{
						if( isset($this->aShippingGroupMap[$value->getShippinggroup()]) == true )
						{
							$aShipGroup[] = $this->getShippingGroupDays(
								$this->aShippingGroupMap[$value->getShippinggroup()]
							);
							
						} else {
							
							// log condition where mapping didn't match
							$this->shipperLogger->postDebug(
								'Plugin_ShipperMapper',
								'Error: missing shipping group',
								print_r ($buyRequest, true) .	print_r ($this->aShippingGroupMap, true)
							);
						}
					}
				}
			}

			// look for dynamic id in buy request
			$productId = $buyRequest->getDynamicBundledItemId();
			
			if($productId > 0)
			{
				// load the product to get ship group
				$product = $this->productRepository->getById($productId);
				
				$aShipGroup[] = $this->getShippingGroupDays(
					$this->aShippingGroupMap[$product->getShipperhqShippingGroup()]
				);
			}
			
			
		}
		
		if(count($aShipGroup) == 0)
		{
			// set default ship date to 5 days
			$aShipGroup[0] = 5;
			
			$this->shipperLogger->postDebug(
				'Plugin_ShipperMapper',
				'error: aShipGroup empty, no valid ship groups for: ' . $item->getProduct()->getSku() . ', setting default ship group to 5 Day',
				$aShipGroup
			);
		}
		
		$max = max($aShipGroup);
			
		foreach($result as &$item)
		{
			$item->attributes[0]['value'] = $max . ' Day';
		}
		
        return $result;
    }
	
	private function getShippingGroupMap()
	{
		$this->aShippingGroupMap = array();
		
		// load the shipping group attribute
		$shippingGroupAttribute = $this->eavAttributeRepository->get(
			\Magento\Catalog\Api\Data\ProductAttributeInterface::ENTITY_TYPE_CODE,
			'shipperhq_shipping_group'
		);
		
		// get the options
		$shippingGroupOptions = $shippingGroupAttribute->getSource()->getAllOptions(false);
		
		foreach($shippingGroupOptions as $group)
		{
			$this->aShippingGroupMap[$group['value']] = $group['label'];
		}
	}
	
	private function getShippingGroupDays($groupName = '5 Days')
	{
		return $this->aShipGroupDays[$groupName];
	}
}