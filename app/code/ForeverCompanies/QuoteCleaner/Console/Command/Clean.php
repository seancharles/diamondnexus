<?php

namespace ForeverCompanies\QuoteCleaner\Console\Command;

use ForeverCompanies\QuoteCleaner\Helper\Cleaner;
use Magento\Framework\App\Area;
use Magento\Framework\App\State;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Clean console class
 */
class Clean extends Command
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var State
     */
    private $state;

    /**
     * @var DateTime
     */
    private $dateTime;

    /**
     * @var Cleaner
     */
    private $cleanerHelper;

    /**
     * @var InputInterface
     */
    protected $input;

    /**
     * @var OutputInterface
     */
    protected $output;

    /**
     * Clean constructor.
     * @param LoggerInterface $logger
     * @param State $state
     * @param DateTime $dateTime
     * @param Cleaner $cleanerHelper
     */
    public function __construct(
        LoggerInterface $logger,
        State $state,
        DateTime $dateTime,
        Cleaner $cleanerHelper
    ) {
        $this->logger = $logger;
        $this->state = $state;
        $this->dateTime = $dateTime;
        $this->cleanerHelper = $cleanerHelper;
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     * @throws LocalizedException
     */
    protected function execute(
        InputInterface $input,
        OutputInterface $output
    ) {
        $this->input = $input;
        $this->output = $output;

        $this->state->setAreaCode(Area::AREA_GLOBAL);

        $clean = $input->getArgument('clean') ?: false;

        if ($clean) {
            $this->output->writeln((string)__('[%1] Start', $this->dateTime->gmtDate()));
            $this->output->writeln('Cleaning customer quotes');
            $result = $this->cleanerHelper->cleanCustomerQuotes();
            $this->output->writeln(
                (string)__(
                    'Result: in %1 sec. cleaned %2 customer quotes',
                    $result['quote_duration'],
                    $result['quote_count']
                )
            );
            $this->output->writeln('Cleaning anonymous quotes');
            $result = $this->cleanerHelper->cleanAnonymousQuotes();
            $this->output->writeln(
                (string)__(
                    'Result: in %1 sec. cleaned %2 anonymous quotes',
                    $result['quote_duration'],
                    $result['quote_count']
                )
            );
            $this->output->writeln((string)__('[%1] Finish', $this->dateTime->gmtDate()));
        }
    }

    /**
     * {@inheritdoc}
     * ForeverCompanies:quote:cleaner [-l|--limit [LIMIT]] [--] <clean>.
     */
    protected function configure()
    {
        $this->setName('forevercompanies:quote:cleaner');
        $this->setDescription('Clean old quote from Magento');
        $this->setDefinition(
            [
            new InputArgument('clean', InputArgument::REQUIRED, 'Clean'),
            new InputOption('limit', '-l', InputOption::VALUE_OPTIONAL, 'Limit'),
            ]
        );
        parent::configure();
    }
}
