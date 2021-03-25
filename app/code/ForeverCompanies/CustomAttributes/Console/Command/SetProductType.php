<?php

declare(strict_types=1);

namespace ForeverCompanies\CustomAttributes\Console\Command;

use ForeverCompanies\CustomAttributes\Helper\ProductType;
use ForeverCompanies\CustomAttributes\Helper\TransformData;
use Magento\Catalog\Model\ResourceModel\Product\Action;
use Magento\Framework\Exception\LocalizedException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SetProductType extends Command
{

    /**
     * @var string
     */
    protected $name = 'forevercompanies:set-product-type';

    /**
     * @var
     */
    protected $helper;

    /**
     * @var ProductType
     */
    protected $productTypeHelper;

    /**
     * @var Action
     */
    protected $productActionObject;

    /**
     * SetProductType constructor.
     * @param TransformData $helper
     * @param ProductType $productTypeHelper
     * @param Action $action
     */
    public function __construct(
        TransformData $helper,
        ProductType $productTypeHelper,
        Action $action
    ) {
        $this->helper = $helper;
        $this->productTypeHelper = $productTypeHelper;
        $this->productActionObject = $action;
        parent::__construct($this->name);
    }

    /**
     * {@inheritdoc}
     * @throws LocalizedException
     * @throws \Exception
     */
    protected function execute(
        InputInterface $input,
        OutputInterface $output
    ) {
        $output->writeln("Get products to set product type...");
        $productCollection = $this->helper->getAllEnabledProducts();
        $output->writeln('Number of products found: ' . $productCollection->count());

        $productTypeData = [];

        foreach ($productCollection->getItems() as $item) {
            $output->writeln('In process product ID = ' . $item->getData('entity_id'));
            $productType = $this->productTypeHelper->getProductType($item);
            if ($productType !== "") {
                $productTypeData[$productType][] = (int)$item->getData('entity_id');
            }
        }

        try {
            if (!empty($productTypeData)) {
                foreach ($productTypeData as $productType => $entityIds) {
                    $this->productActionObject->updateAttributes(
                        $entityIds,
                        ['product_type' => $productType],
                        0
                    );
                }
            }
        } catch (\Exception $e) {
            $output->writeln("Error: " . $e->getMessage());
        }

        $output->writeln('Product Types are updated! Please execute bin/magento cache:clean');
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName($this->name);
        $this->setDescription("Set Product Types - update the product_type attribute");
        parent::configure();
    }
}
