<?php

namespace ForeverCompanies\ConfigurableOrderItems\Console\Command;

use Magento\Framework\App\State;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SortOrderBuilder;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\App\ResourceConnection;

/**
 * Class FormatOrderItems
 *  Run with no parameters to process all orders in the range specified by defaults
 *  To run a date range pass the parameter --start and --end with a valid date
 *  Example:
 *      bin/magento forevercompanies:formatconfigorderitems --start 2021-01-01 --end 2021-06-01
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

    protected $_startDate;
    protected $_endDate;
    
    const PAGE_SIZE = 2500;
    const START_DATE = 'start';
    const END_DATE = 'end';

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

        $this->addOption(
            self::START_DATE,
            null,
            InputOption::VALUE_OPTIONAL,
            'Start Date'
        );

        $this->addOption(
            self::END_DATE,
            null,
            InputOption::VALUE_OPTIONAL,
            'End Date'
        );

        parent::configure();
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

        $this->getInputs($input);
        $totalOrders = $this->getOrderCount();

        $connection  = $this->_resourceConnection->getConnection();
        $sortOrder = $this->_sortOrderBuilder->setField('entity_id')->setDirection('DESC')->create();

        if ($totalOrders > 0) {
            echo $totalOrders . " orders found " . $this->_startDate . " through " . $this->_endDate . "\n";

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
                            $this->_startDate,
                            'gt'
                        )
                        ->addFilter(
                            'created_at',
                            $this->_endDate,
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

    protected function getInputs(InputInterface $input) {
        $startDate = $input->getOption(self::START_DATE);
        $endDate = $input->getOption(self::END_DATE);

        if (!strlen($startDate) > 0) {
            $this->_startDate = '2011-09-16';
        } else {
            $this->_startDate = $startDate;
        }

        if (!strlen($endDate) > 0) {
            $this->_endDate = '2021-06-01';
        } else {
            $this->_endDate = $endDate;
        }
    }

    protected function getOrderCount() {
        
        $countCriteria = $this->_searchCriteriaBuilder
            // filter M1 launch all legacy products are no longer supported
            ->addFilter(
                'created_at',
                $this->_startDate,
                'gt'
            )
            ->addFilter(
                'created_at',
                $this->_endDate,
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
}
