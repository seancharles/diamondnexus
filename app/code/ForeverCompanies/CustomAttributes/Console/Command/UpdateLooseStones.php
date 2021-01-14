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

class UpdateLooseStones extends AbstractCommand
{

    /**
     * @var string
     */
    protected $name = 'forevercompanies:update-loose-stones';

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
        $output->writeln("Get products for change loose stones...");
        $productCollection = $this->helper->getProductsForChangeLooseStones();
        $output->writeln('Products for transformation: ' . $productCollection->count());
        foreach ($productCollection->getItems() as $item) {
            $output->writeln('In process product SKU = ' . $item->getData('sku'));
            $this->helper->updateLooseStone($item->getData('sku'));
        }
        $output->writeln('Loose stones are updated! Please execute bin/magento cache:clean');
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName($this->name);
        $this->setDescription("Update loose stones - update empty attributes");
        parent::configure();
    }
}
