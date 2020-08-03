<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace ForeverCompanies\CustomAttributes\Console\Command;

use ForeverCompanies\CustomAttributes\Helper\TransformData;
use Magento\Catalog\Api\AttributeSetRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ProductRepository;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Eav\Model\Config;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Zend_Db_Select;

class TransformAttributes extends Command
{

    /**
     * @var TransformData
     */
    protected $helper;

    /**
     * @var string
     */
    protected $name = 'forevercompanies:transform-attributes';

    public function __construct(
        TransformData $helper
    )
    {
        $this->helper = $helper;
        parent::__construct($this->name);
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(
        InputInterface $input,
        OutputInterface $output
    )
    {
        $output->writeln("Get products for transformation...");
        $productCollection = $this->helper->getProductsForTransformCollection();
        $output->writeln('Products for transformation: ' . $productCollection->count());
        foreach ($productCollection->getItems() as $item) {
            /** @var Product $product */
            $this->helper->transformProduct($item->getEntityId());
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName($this->name);
        $this->setDescription("Transform attributes after M1 - M2 migration");
        /*$this->setDefinition([
            new InputArgument(self::NAME_ARGUMENT, InputArgument::OPTIONAL, "Name"),
            new InputOption(self::NAME_OPTION, "-a", InputOption::VALUE_NONE, "Option functionality")
        ]);*/
        parent::configure();
    }
}
