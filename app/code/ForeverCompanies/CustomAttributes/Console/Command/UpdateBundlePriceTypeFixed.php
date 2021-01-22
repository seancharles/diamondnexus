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

class UpdateBundlePriceTypeFixed extends AbstractCommand
{

    /**
     * @var string
     */
    protected $name = 'forevercompanies:update-bundle-price-type-fixed';

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
        $output->writeln("Get bundle products to update price_type attribute...");
        $productCollection = $this->helper->getBundleProducts();
        $output->writeln('Products for update: ' . $productCollection->count());
        foreach ($productCollection->getItems() as $item) {
            $output->writeln('In process product ID = ' . $item->getData('entity_id'));
            $this->helper->updateBundlePriceTypeFixed((int) $item->getData('entity_id'));
        }
        $output->writeln('Bundle price_type is updated! Please execute bin/magento cache:flush');
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName($this->name);
        $this->setDescription("Update bundle price_type attribute to be fixed pricing");
        parent::configure();
    }
}
