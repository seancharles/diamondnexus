<?php

namespace ForeverCompanies\ConfigurableOrderItems\Console\Command;

use Magento\Framework\App\State;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class SomeCommand
 */
class FormatOrderItems extends Command
{
    /**
     * @var string
     */
    protected $name = 'forevercompanies:formatconfigorderitems';

    protected $state;

    public function __construct(
        State $state
    ) {
        parent::__construct($this->name);
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName($this->name);
        $this->setDescription("Format magento configurable orders items after migration to M2 format");
        parent::configure();
    }

    /**
     * Execute the command
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return null|int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->state->setAreaCode(\Magento\Framework\App\Area::AREA_ADMINHTML);

        echo "Reformat config order items...";
    }
}
