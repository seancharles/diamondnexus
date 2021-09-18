<?php

namespace ForeverCompanies\LooseStoneImport\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use ForeverCompanies\LooseStoneImport\Model\StoneDisable;
use Magento\Framework\App\State;
use Magento\Catalog\Model\Product\Action as ProductAction;

class DisableStones extends Command
{
    
    const NAME = 'disable_stones';
    
    protected $stoneDisableModel;
    private $state;
    
    public function __construct(
        StoneDisable $stoneD,
        State $st
        ) {
            $this->stoneDisableModel = $stoneD;
            $this->state = $st;
            parent::__construct('forevercompanies:disable-stones');
    }
    
    /**
     * @inheritDoc
     */
    protected function configure()
    {
        $this->setName('forevercompanies:disable-stones');
        $this->setDescription('Disables Stones');
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
        
        $this->stoneDisableModel->run();
        return;
    }
}