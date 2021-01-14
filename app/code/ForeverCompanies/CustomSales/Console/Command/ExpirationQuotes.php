<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace ForeverCompanies\CustomSales\Console\Command;

use ForeverCompanies\CustomSales\Cron\ExpirationDate;
use Magento\Framework\App\Area;
use Magento\Framework\App\State;
use Magento\Framework\Exception\LocalizedException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ExpirationQuotes extends Command
{
    /**
     * @var State
     */
    protected $state;

    /**
     * @var ExpirationDate
     */
    protected $expirationDate;

    /**
     * @var string
     */
    protected $name = 'forevercompanies:expiration-quotes';

    /**
     * TransformAttributes constructor.
     * @param State $state
     * @param ExpirationDate $expirationDate
     */
    public function __construct(
        State $state,
        ExpirationDate $expirationDate
    ) {
        $this->state = $state;
        $this->expirationDate = $expirationDate;
        parent::__construct($this->name);
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return void
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $this->state->setAreaCode(Area::AREA_GLOBAL);
            $output->writeln("Starting archive quotes with expiration date...");
            $this->expirationDate->execute();
            $output->writeln("Done! Please execute command 'bin/magento cache:flush'");
        } catch (LocalizedException $e) {
            $output->writeln($e->getMessage());
        } catch (\Exception $e) {
            $output->writeln('Can\'t move to archive quotes: ' . $e->getMessage());
        }
    }
}
