<?php
namespace ForeverCompanies\CustomAttributes\Console\Command;

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
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var \Magento\Catalog\Api\Data\ProductInterface
     */
    protected $productRepository;

    /**
     * @var \Magento\Eav\Model\Config
     */
    protected $eavConfig;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Action
     */
    protected $productActionObject;

    /**
     * @var Array
     */
    protected $shippingStatusTranslateMap = [
        "SixDay" => "10 Days"
    ];

    /**
     * @var Array
     */
    protected $shippingStatusLabelMap = [];

    /**
     * TransformMultiselect constructor.
     * @param State $state
     * @param ProductRepositoryInterface $productRepository
     */
    public function __construct(
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $collectionFactory,
        \Magento\Catalog\Api\Data\ProductInterface $productRepository,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Catalog\Model\ResourceModel\Product\Action $action
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
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $output->writeln("Updating products Shipping Group with mapped Shipping Status value.");

        // get shipping status attribute to create mapping
        $attribute = $this->eavConfig->getAttribute('catalog_product', 'shipping_status');

        $shippingStatusOptions = $attribute->getSource()->getAllOptions();

        foreach ($shippingStatusOptions as $option) {
            if ($option['value']) {
                $this->shippingStatusLabelMap[$option['label']] = $option['value'];
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
                    'shipperhq_shipping_group' => $this->shippingStatusTranslateMap[$shippingStatusKey]
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
