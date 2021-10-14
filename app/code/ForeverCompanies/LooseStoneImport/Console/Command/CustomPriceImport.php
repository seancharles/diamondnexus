<?php

namespace ForeverCompanies\LooseStoneImport\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use ForeverCompanies\LooseStoneImport\Model\StoneCustomPriceImport;
use Magento\Framework\App\State;

class CustomPriceImport extends Command
{
    public const NAME = 'stones_custom_price_import';

    protected StoneCustomPriceImport $stoneCustomPriceImport;
    private State $state;

    public function __construct(
        StoneCustomPriceImport $stoneCustomPriceImport,
        State $state
    ) {
        $this->stoneCustomPriceImport = $stoneCustomPriceImport;
        $this->state = $state;
        parent::__construct('forevercompanies:stones-custom-price-import');
    }

    /**
     * @inheritDoc
     */
    protected function configure()
    {
        $this->setName('forevercompanies:stones-custom-price-import');
        $this->setDescription('Loose stone custom price import (Samwise)');
        $this->addOption(
            self::NAME,
            null,
            InputOption::VALUE_REQUIRED,
            'Name'
        );
        parent::configure();
    }

    /**
     * @inheritDoc
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->state->setAreaCode(\Magento\Framework\App\Area::AREA_FRONTEND);

        if ($name = $input->getOption(self::NAME)) {
            $output->writeln('<info>Provided name is `' . $name . '`</info>');
        }

        $this->stoneCustomPriceImport->run();
        return;
    }
}