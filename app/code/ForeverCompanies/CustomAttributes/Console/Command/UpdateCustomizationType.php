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

class UpdateCustomizationType extends AbstractCommand
{

    /**
     * @var string
     */
    protected $name = 'forevercompanies:update-customization-type';

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
        $output->writeln("Get products for update customization type...");
        $productCollection = $this->helper->getProductsAfterTransformCollection();
        $output->writeln('Products for update customization type: ' . $productCollection->count());
        foreach ($productCollection->getItems() as $item) {
            $output->writeln('In process product ID = ' . $item->getData('entity_id'));
            $this->helper->transformProductOptions((int)$item->getData('entity_id'));
        }
        $output->writeln('Update is complete! Please execute bin/magento cache:clean');
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName($this->name);
        $this->setDescription("Update missing customization types");
        parent::configure();
    }
}
