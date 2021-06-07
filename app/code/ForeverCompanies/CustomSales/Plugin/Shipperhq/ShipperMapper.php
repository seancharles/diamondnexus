<?php
namespace ForeverCompanies\CustomSales\Plugin\Shipperhq;

class ShipperMapper
{
    protected $shipperLogger;
    protected $productRepository;
    protected $itemoption;
    
    protected $aShipGroupDays = [
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
    ];
    
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

        $aShipGroup = [];

        foreach ($magentoItems as $item) {
            $buyRequest = $item->getBuyRequest();

            $productShippingGroup = $item->getProduct()->getShipperhqShippingGroup();

            if ($productShippingGroup) {
                $aShipGroup[] = $this->getShippingGroupDays(
                    $this->aShippingGroupMap[$productShippingGroup]
                );
            }

            $options = $this->productOptionRepository->getProductOptions($item->getProduct());

            foreach ($options as $option) {
                $values = $option->getValues();
                
                foreach ($values as $value) {
                    if ($value->getShippinggroup() > 0) {
                        if ($buyRequest['options'][$option['option_id']] == $value->getId()) {
                            if (isset($this->aShippingGroupMap[$value->getShippinggroup()]) == true) {
                                $aShipGroup[] = $this->getShippingGroupDays(
                                    $this->aShippingGroupMap[$value->getShippinggroup()]
                                );
                                
                            }
                        }
                    }
                }
            }

            // look for dynamic id in buy request
            $productId = $buyRequest->getDynamicBundledItemId();
            
            if ($productId > 0) {
                // load the product to get ship group
                $product = $this->productRepository->getById($productId);
                
                $bundledItemShipGroup = $product->getShipperhqShippingGroup();
                
                if ($bundledItemShipGroup > 0) {
                    $aShipGroup[] = $this->getShippingGroupDays(
                        $this->aShippingGroupMap[$product->getShipperhqShippingGroup()]
                    );
                }
            }
        }
        
        if (count($aShipGroup) == 0) {
            // set default ship date to 5 days
            $aShipGroup[0] = 5;
        }
        
        $max = max($aShipGroup);
        
        foreach ($result as &$item) {
            $item->attributes[0]['value'] = $max . ' Day';
        }
        
        return $result;
    }
    
    private function getShippingGroupMap()
    {
        $this->aShippingGroupMap = [];
        
        // load the shipping group attribute
        $shippingGroupAttribute = $this->eavAttributeRepository->get(
            \Magento\Catalog\Api\Data\ProductAttributeInterface::ENTITY_TYPE_CODE,
            'shipperhq_shipping_group'
        );
        
        // get the options
        $shippingGroupOptions = $shippingGroupAttribute->getSource()->getAllOptions(false);
        
        foreach ($shippingGroupOptions as $group) {
            $this->aShippingGroupMap[$group['value']] = $group['label'];
        }
    }
    
    private function getShippingGroupDays($groupName = '5 Days')
    {
        return $this->aShipGroupDays[$groupName];
    }
}
