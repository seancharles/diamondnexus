<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace ForeverCompanies\CustomAttributes\Console\Command;

use Magento\Framework\App\Area;
use Magento\Framework\Exception\LocalizedException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DiamondOptionsCleanup extends AbstractCommand
{

    /**
     * @var string
     */
    protected $name = 'forevercompanies:diamond-options-cleanup';

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
        $output->writeln("Get products for clean options...");
        $productCollection = $this->helper->getMigrationLooseDiamondsProducts();
        $output->writeln('Products for cleaning: ' . $productCollection->count());
        foreach ($productCollection->getItems() as $item) {
            $output->writeln('In process product ID = ' . $item->getData('entity_id'));
            $this->helper->cleanOptions($item->getData('sku'));
        }
        $output->writeln('Migration loose diamond\'s options cleared! Execute bin/magento indexer:reindex');
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName($this->name);
        $this->setDescription("Clean options in diamonds with title 'Set'");
        parent::configure();
    }
}
