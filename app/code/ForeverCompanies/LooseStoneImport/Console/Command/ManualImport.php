<?php
declare(strict_types=1);

namespace ForeverCompanies\LooseStoneImport\Console\Command;

use ForeverCompanies\LooseStoneImport\Model\StoneImport;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Framework\App\State;

class ManualImport extends Command
{
    protected $stoneImportModel;
    protected $name;
    private $state;

    public function __construct(
        StoneImport $stoneImport,
        State $state
    ) {
        parent::__construct('forevercompanies:manual-diamond-import');
            
        
        $this->state = $state;
        
        $this->stoneImportModel = $stoneImport;
        $this->name = 'forevercompanies:manual-diamond-import';
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $this->state->setAreaCode('frontend');
        $this->stoneImportModel->run();
        return;
    }
    
    protected function configure()
    {
        $this->setName('forevercompanies:manual-diamond-import');
        $this->setDescription("Manual loose stone import. Mainly for testing, but may as well keep it around.");
        parent::configure();
    }
}
