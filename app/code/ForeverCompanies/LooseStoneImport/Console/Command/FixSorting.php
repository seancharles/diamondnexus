<?php

namespace ForeverCompanies\LooseStoneImport\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use ForeverCompanies\LooseStoneImport\Model\StoneSort;
use Magento\Framework\App\State;

class FixSorting extends Command
{
    
    const NAME = 'fix_stone_sorting';
    
    protected $stoneSortingModel;
    private $state;
    
    public function __construct(
        StoneSort $stoneS,
        State $st
        ) {
            $this->stoneSortingModel = $stoneS;
            $this->state = $st;
            parent::__construct('forevercompanies:fix-stone-sorting');
    }
    
    /**
     * @inheritDoc
     */
    protected function configure()
    {
        $this->setName('forevercompanies:fix-stone-sorting');
        $this->setDescription('Fix Stone Sorting');
        $this->addOption(
            self::NAME,
            null,
            InputOption::VALUE_REQUIRED,
            'Name'
            );
        parent::configure();
    }
    
    
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->state->setAreaCode(\Magento\Framework\App\Area::AREA_FRONTEND);
        
        if ($name = $input->getOption(self::NAME)) {
            $output->writeln('<info>Provided name is `' . $name . '`</info>');
        }
        
        $this->stoneSortingModel->run();
        return;
    }
}