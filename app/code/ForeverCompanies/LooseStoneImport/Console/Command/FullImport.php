<?php

namespace ForeverCompanies\LooseStoneImport\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use ForeverCompanies\LooseStoneImport\Model\StoneImport;
use Magento\Framework\App\State;

class FullImport extends Command
{
    const NAME = 'full_stone_import';
    
    protected $stoneImportModel;
    private $state;
    
    public function __construct(
        StoneImport $stoneImport,
        State $st
        ) {
            $this->stoneImportModel = $stoneImport;
            $this->state = $st;
            parent::__construct('forevercompanies:full-stone-import');
    }
    
    /**
     * @inheritDoc
     */
    protected function configure()
    {
        $this->setName('forevercompanies:full-stone-import');
        $this->setDescription('Complete Loose Stone Import');
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
        
        $this->stoneImportModel->run(true);
        return;
    }
}