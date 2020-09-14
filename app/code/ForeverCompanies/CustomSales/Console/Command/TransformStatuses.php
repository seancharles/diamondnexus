<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace ForeverCompanies\CustomSales\Console\Command;

use ForeverCompanies\CustomSales\Helper\TransformData;
use Magento\Framework\App\Area;
use Magento\Framework\App\State;
use Magento\Framework\Exception\LocalizedException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class TransformStatuses extends Command
{

    /**
     * @var TransformData
     */
    protected $helper;

    /**
     * @var State
     */
    protected $state;

    /**
     * @var string
     */
    protected $name = 'forevercompanies:transform-orders';

    /**
     * TransformAttributes constructor.
     * @param State $state
     * @param TransformData $helper
     */
    public function __construct(
        State $state,
        TransformData $helper
    ) {
        $this->state = $state;
        $this->helper = $helper;
        parent::__construct($this->name);
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|void|null
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $this->state->setAreaCode(Area::AREA_GLOBAL);

            $output->writeln("Starting change orders statuses...");

            foreach ($this->helper->getStatusesForDelete() as $old => $new) {
                $output->writeln("Change status '$old' to '$new' ...");
                $this->helper->changeOrderStatus($old, $new);
            }
            $this->helper->deleteStatuses();
            $output->writeln("Done! Please execute command 'bin/magento indexer:reindex'");
        } catch (LocalizedException $e) {
            $output->writeln($e->getMessage());
        }
    }
}
