<?php
namespace ForeverCompanies\CustomAttributes\Console\Command;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Product\Action;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Eav\Model\Config;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Framework\Exception\LocalizedException;

class TranslateShipStatusToShippingGroup extends Command
{
    /**
     * @var string
     */
    protected $name = 'forevercompanies:translate:shipping-status';

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var ProductInterface
     */
    protected $productRepository;

    /**
     * @var Config
     */
    protected $eavConfig;

    /**
     * @var Action
     */
    protected $productActionObject;

    /**
     * @var array
     */
    protected $shippingStatusTranslateMap = [
        "ZeroDay" => "0 Day",
        "Last Minute" => "0 Day",
        "Immediate" => "1 Day",
        "TwoDay" => "2 Day",
        "ThreeDay" => "3 Day",
        "FourDay" => "4 Day",
        "WarrantyFour" => "4 Day",
        "Rapid" => "5 Day",
        "SixDay" => "6 Day",
        "SevenDay" => "7 Day",
        "Warranty" => "7 Day",
        "Standard" => "8 Day",
        "TenDay" => "10 Day",
        "Extended" => "12 Day",
        "FourteenDay" => "14 Day",
        "FifteenDay" => "15 Day",
        "Backordered" => "17 Day",
        "TwentyDay" => "20 Day",
        "TwentyOneDay" => "20 Day",
        // fifty day isn't supported by any shipping api (update to 20)
        "FiftyDay" => "20 Day"
    ];

    /**
     * @var array
     */
    protected $shippingStatusLabelMap = [];
    private $shipperGroupLabelMap;

    /**
     * TransformMultiselect constructor.
     * @param CollectionFactory $collectionFactory
     * @param ProductInterface $productRepository
     * @param Config $eavConfig
     * @param Action $action
     */
    public function __construct(
        CollectionFactory $collectionFactory,
        ProductInterface $productRepository,
        Config $eavConfig,
        Action $action
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->productRepository = $productRepository;
        $this->eavConfig = $eavConfig;
        $this->productActionObject = $action;

        parent::__construct($this->name);
    }

    /**
     * {@inheritdoc}
     * @throws LocalizedException
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln("Updating products Shipping Group with mapped Shipping Status value.");

        // get shipping status attribute to create mapping
        $shipStatusAttribute = $this->eavConfig->getAttribute('catalog_product', 'shipping_status');

        $shippingStatusOptions = $shipStatusAttribute->getSource()->getAllOptions();

        foreach ($shippingStatusOptions as $option) {
            if ($option['value']) {
                $this->shippingStatusLabelMap[$option['label']] = $option['value'];
            }
        }

        // get shipping group option ids
        $hqCode = 'shipperhq_shipping_group';
        $shipperGroupAttribute = $this->eavConfig->getAttribute(Product::ENTITY, $hqCode);

        $shipperGroupOptions = $shipperGroupAttribute->getSource()->getAllOptions();

        foreach ($shipperGroupOptions as $option) {
            if ($option['value']) {
                $this->shipperGroupLabelMap[$option['label']] = $option['value'];
            }
        }

        foreach ($this->shippingStatusLabelMap as $shippingStatusKey => $shippingStatusId) {
            $productIds = [];

            $productCollection = $this->collectionFactory->create();
            $productCollection->addFieldToFilter("shipping_status", $shippingStatusId);

            $text = " Product(s) found with shipping status code: ";
            $output->writeln($productCollection->getSize() . $text . $shippingStatusKey);

            foreach ($productCollection as $product) {
                $productIds[] = $product->getId();
            }

            $this->productActionObject->updateAttributes(
                $productIds,
                [
                    /** Where is $str ? */
                    $str => $this->shipperGroupLabelMap[
                        $this->shippingStatusTranslateMap[$shippingStatusKey]
                    ]
                ],
                0
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName($this->name);
        $this->setDescription("Translate product shipping status to shipping group");
        parent::configure();
    }
}
