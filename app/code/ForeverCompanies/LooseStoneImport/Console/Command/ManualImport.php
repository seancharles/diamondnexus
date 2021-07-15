<?php
declare(strict_types=1);

namespace ForeverCompanies\LooseStoneImport\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use ForeverCompanies\LooseStoneImport\Model\StoneImport;

class ManualImport extends Command
{
    
    const NAME = 'run_stone_import';
    
    protected $stoneImportModel;
    
    public function __construct(
        StoneImport $stoneImport
        ) {
            $this->stoneImportModel = $stoneImport;
            parent::__construct('forevercompanies:manual-diamond-import');
    }
    
    /**
     * @inheritDoc
     */
    protected function configure()
    {
        $this->setName('forevercompanies:manual-diamond-import');
        $this->setDescription('Manual Loose Stone Import');
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
        if ($name = $input->getOption(self::NAME)) {
            $output->writeln('<info>Provided name is `' . $name . '`</info>');
        }
        
        $this->stoneImportModel->run();
        return;
    }
}