<?php

namespace ForeverCompanies\ConfigurableOrderItems\Console\Command;

use Magento\Framework\App\Area;
use Magento\Framework\App\State;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SortOrderBuilder;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\App\ResourceConnection;

/**
 * Class SomeCommand
 */
class FormatOrderItems extends Command
{
    /**
     * @var string
     */
    protected $name = 'forevercompanies:formatconfigorderitems';

    protected $_attributeRepository;
    protected $_orderRepository;
    protected $_searchCriteriaBuilder;
    protected $_sortOrderBuilder;
    protected $_state;
    protected $_jsonHelper;
    protected $_resourceConnection;
    
    const PAGE_SIZE = 100;

    public function __construct(
        ProductAttributeRepositoryInterface $attributeRepository,
        OrderRepositoryInterface $orderRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        SortOrderBuilder $sortOrderBuilder,
        State $state,
        Json $jsonHelper,
        ResourceConnection $resourceConnection
    ) {
        $this->_attributeRepository = $attributeRepository;
        $this->_orderRepository = $orderRepository;
        $this->_searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->_sortOrderBuilder = $sortOrderBuilder;
        $this->_state = $state;
        $this->_jsonHelper = $jsonHelper;
        $this->_resourceConnection = $resourceConnection;

        parent::__construct($this->name);
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName($this->name);
        $this->setDescription("Format magento configurable orders items after migration to M2 format");
        parent::configure();
    }

    protected function getOrderCount() {
        
        $countCriteria = $this->_searchCriteriaBuilder
            // filter M1 launch all legacy products are no longer supported
            // TBD: this might need to change if the process of updating older orders becomes problematic
            ->addFilter(
                'created_at',
                '2011-09-16',
                'gt'
            )
            ->addFilter(
                'created_at',
                '2021-06-01',
                'lt'
            )
            ->setCurrentPage(1)
            ->setPageSize(1)
            ->create();

        $countResult = $this->_orderRepository->getList($countCriteria);

        return $countResult->getTotalCount();
    }

    protected function reformatBuyRequest($item) {

        $buyRequest = $item->getBuyRequest()->toArray();

        $newBuyRequest = [
            'info_buyRequest' => $this->_jsonHelper->serialize([
                'item' => $item->getProductId(),
                'product' => $item->getProductId(),
                'qty' => $item->getQtyOrdered(),
                'related_product' => "",
                'selected_configurable_option' => 0,
                'super_attribute' => $buyRequest['super_attribute'],
                'options' => $item->getOptions()
            ]),
            'attributes_info' => [],
            'simple_name' => $item->getName(),
            'simple_sku' => $item->getSku(),
            'product_calculations' => 1,
            'shipment_type' => 0,
            "giftcard_email_template" => null,
	        "giftcard_is_redeemable" => 0,
	        "giftcard_lifetime" => null,
	        "giftcard_type" => null
        ];
        
        foreach($buyRequest['super_attribute'] as $attributeId => $attributeOptionId) {
            
            $attribute = $this->_attributeRepository->get($attributeId);
            
            $newBuyRequest['attributes_info'][] = [
                "label" => $attribute->getStoreLabel(),
                "option_id" => $attributeId,
                "option_value" => $attributeOptionId,
                "value" => $attribute->getSource()->getOptionText($attributeOptionId)
            ];
        }

        return $this->_jsonHelper->serialize($newBuyRequest);
    }

    /**
     * Execute the command
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return null|int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->_state->setAreaCode(\Magento\Framework\App\Area::AREA_ADMINHTML);

        $sortOrder = $this->_sortOrderBuilder->setField('entity_id')->setDirection('DESC')->create();

        $totalOrders = $this->getOrderCount();

        $connection  = $this->_resourceConnection->getConnection();

        if ($totalOrders > 0) {
            echo $totalOrders . " orders found\n";
            
            // calculate number of pages
            $pageCount = ceil($totalOrders / self::PAGE_SIZE);
            
            for ($i=1; $i<=$pageCount; $i++) {
                try {
                    $startTime = time();

                    $searchCriteria = $this->_searchCriteriaBuilder
                        ->setSortOrders([$sortOrder])
                        ->setCurrentPage($i)
                        ->setPageSize(self::PAGE_SIZE)
                        ->addFilter(
                            'created_at',
                            '2011-09-16',
                            'gt'
                        )
                        ->addFilter(
                            'created_at',
                            '2021-06-01',
                            'lt'
                        )
                        ->create();

                    echo "getting batch list: " . (($i-1) * self::PAGE_SIZE) . " - " . ($i * self::PAGE_SIZE) . "\n";

                    $ordersResult = $this->_orderRepository->getList($searchCriteria);
                    
                    if ($ordersResult->getTotalCount() > 0) {
                        foreach ($ordersResult->getItems() as $order) {
                            $orderItems = $order->getAllItems();
                            foreach ($orderItems as $item) {
                                if ($item && $item->getData('is_translated_m2') == 0) {
                                    if ($item->getProductType() == 'configurable') {
                                        $newBuyRequest = $this->reformatBuyRequest($item);

                                        $sql = "UPDATE
                                                    sales_order_item
                                                SET
                                                    m1_buy_request = product_options,
                                                    product_options = '" . $newBuyRequest . "',
                                                    is_translated_m2 = '1'
                                                WHERE
                                                      item_id = '" . $item->getItemId() . "';";

                                        echo $sql . "\n";

                                        $connection->query($sql);
                                    } else {
                                        echo "simple\n";
                                    }
                                } else {
                                    // log error
                                    echo "item error" . $item->getItemId() . "\n";
                                }
                            }
                        };
                    }
                    
                    echo "processed in " . (time() - $startTime) . " seconds\n";
                    
                } catch (Exception $e) {
                    echo $e->getMessage();
                }
            }
        }

        echo "Reformat config order items...";
    }
}
