<?php

namespace ForeverCompanies\LooseStoneImageScript\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Framework\App\State;

class StoneImages extends Command
{
    const NAME = 'import_stone_images';
    
    private $state;
    
    public function __construct(
        State $st
        ) {
            $this->state = $st;
            parent::__construct('forevercompanies:loose-stone-image-import');
    }
    
    /**
     * @inheritDoc
     */
    protected function configure()
    {
        $this->setName('forevercompanies:loose-stone-image-import');
        $this->setDescription('Loose Stone Image Import');
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
        
        echo 'ok here we are';
        return;
    }
}