<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace ForeverCompanies\CustomAttributes\Console\Command;

use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Framework\App\Area;
use Magento\Framework\App\State;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\StateException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class TransformAttributes extends AbstractCommand
{

    /**
     * @var string
     */
    protected $name = 'forevercompanies:transform-attributes';

    /**
     * {@inheritdoc}
     * @throws LocalizedException
     */
    protected function execute(
        InputInterface $input,
        OutputInterface $output
    ) {
        try {
            $this->state->getAreaCode();
        } catch (LocalizedException $e) {
            $this->state->setAreaCode(Area::AREA_GLOBAL);
        }
        $output->writeln("Get products for transformation...");
        $output->writeln("Configurable products first...");
        $productCollection = $this->helper->getBundleAndConfigurableProducts();
        $output->writeln('Configurable Products for transformation: ' . $productCollection->count());
        $this->transformProduct($productCollection, $output);
        $output->writeln("And after configurable transform simple products...");
        $productCollection = $this->helper->getProductsAfterTransformCollection();
        $output->writeln('Simple Products for transformation: ' . $productCollection->count());
        $this->transformProduct($productCollection, $output);
        $output->writeln('Transformation is complete! Please execute bin/magento indexer:reindex');
    }

    /**
     * @param Collection $productCollection
     * @param OutputInterface $output
     */
    protected function transformProduct(Collection $productCollection, OutputInterface $output)
    {
        foreach ($productCollection->getItems() as $item) {
            try {
                $output->writeln('In process product ID = ' . $item->getData('entity_id'));
                if ($item->getData('sku') == null) {
                    $output->writeln('SKU NOT FOUND!');
                    continue;
                }
                $this->helper->transformProduct((int)$item->getData('entity_id'));
            } catch (InputException $e) {
                $output->writeln($e->getMessage());
            } catch (NoSuchEntityException $e) {
                $output->writeln($e->getMessage());
            } catch (StateException $e) {
                $output->writeln($e->getMessage());
            } catch (LocalizedException $e) {
                $output->writeln($e->getMessage());
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName($this->name);
        $this->setDescription("Transform attributes after M1 - M2 migration");
        parent::configure();
    }
}
