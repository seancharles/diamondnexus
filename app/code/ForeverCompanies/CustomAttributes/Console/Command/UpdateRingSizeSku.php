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

class UpdateRingSizeSku extends AbstractCommand
{

    /**
     * @var string
     */
    protected $name = 'forevercompanies:update-ring-size-sku';

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
        $output->writeln("Get products for update ring sizes sku in bundles...");
        $productCollection = $this->helper->getBundleProducts();
        $output->writeln('Products for update: ' . $productCollection->count());
        foreach ($productCollection->getItems() as $item) {
            $output->writeln('In process product SKU = ' . $item->getData('sku'));
            $this->helper->updateRingSizeSku($item->getData('sku'));
        }
        $output->writeln('Ring sizes sku updated! Please execute bin/magento cache:clean');
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName($this->name);
        $this->setDescription("Update ring size skus - change sku in bundle products option section");
        parent::configure();
    }
}
