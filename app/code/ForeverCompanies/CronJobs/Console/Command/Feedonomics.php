<?php
namespace ForeverCompanies\CronJobs\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use ForeverCompanies\CronJobs\Model\FeedLogic;

class Feedonomics extends Command
{
    const NAME = 'run_feedonomics';
    
    protected $feedModel;
    
    public function __construct(
        FeedLogic $feed
    ) {
        $this->feedModel = $feed;
        parent::__construct('forevercompanies:feedonomics');
    }

    /**
     * @inheritDoc
     */
    protected function configure()
    {
        $this->setName('forevercompanies:feedonomics');
        $this->setDescription('Run the Early / Late Feedonomics Cron Jobs');
        $this->addOption(
            self::NAME,
            null,
            InputOption::VALUE_REQUIRED,
            'Name'
        );
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
        if ($name = $input->getOption(self::NAME)) {
            $output->writeln('<info>Provided name is `' . $name . '`</info>');
        }
        $this->feedModel->BuildCsvs(1);
        $this->feedModel->BuildCsvs(12);
        return;
    }
}
