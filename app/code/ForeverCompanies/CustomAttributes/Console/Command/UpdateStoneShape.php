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

class UpdateStoneShape extends AbstractCommand
{

    /**
     * @var string
     */
    protected $name = 'forevercompanies:update-stone-shape';

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
        $output->writeln("Get products for update stone shapes...");
        $productCollection = $this->helper->getProductsAfterTransformCollection();
        $output->writeln('Products for update: ' . $productCollection->count());
        foreach ($productCollection->getItems() as $item) {
            if ($item->getData('entity_id') !== null) {
                $output->writeln('In process product ID = ' . $item->getData('entity_id'));
                $this->helper->updateStoneShape($item->getData('entity_id'));
            }
        }
        $output->writeln('Update stones complete! Please execute bin/magento cache:clean');
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName($this->name);
        $this->setDescription("Update stone shape");
        parent::configure();
    }
}
