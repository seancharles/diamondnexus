<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace ForeverCompanies\CustomAttributes\Console\Command;

use Magento\Framework\Exception\LocalizedException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class TransformOptions extends AbstractCommand
{

    /**
     * @var string
     */
    protected $name = 'forevercompanies:transform-options';

    /**
     * {@inheritdoc}
     * @throws LocalizedException
     */
    protected function execute(
        InputInterface $input,
        OutputInterface $output
    ) {
        $this->state->setAreaCode(\Magento\Framework\App\Area::AREA_GLOBAL);
        $output->writeln("Get products for option transformation...");
        $productCollection = $this->helper->getProductsAfterTransformCollection();
        $output->writeln('Products for update options: ' . $productCollection->count());
        foreach ($productCollection->getItems() as $item) {
            $output->writeln('In process product ID = ' . $item->getData('entity_id'));
            $this->helper->transformProductOptions((int)$item->getData('entity_id'));
        }
        $output->writeln('Transformation is complete! Please execute bin/magento indexer:reindex');
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName($this->name);
        $this->setDescription("Transform options after M1 - M2 transformation");
        parent::configure();
    }
}
