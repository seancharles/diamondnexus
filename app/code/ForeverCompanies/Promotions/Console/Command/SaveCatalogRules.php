<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace ForeverCompanies\Promotions\Console\Command;


use Magento\Framework\Exception\LocalizedException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SaveCatalogRules extends AbstractCommand
{

    /**
     * @var string
     */
    protected $name = 'forevercompanies:promotions';

    /**
     * {@inheritdoc}
     * @throws LocalizedException
     */
    protected function execute(
        InputInterface $input,
        OutputInterface $output
    ) {
        $this->state->setAreaCode(\Magento\Framework\App\Area::AREA_GLOBAL);
        $output->writeln("Create cart catalog price conditions...");
        $productCollection = $this->helper->selectProductsWithCustomOptions();
        $output->writeln('Created catalog price rules!');
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName($this->name);
        $this->setDescription("Create catalog price rules with necessary conditions and actions.");
        parent::configure();
    }
}
