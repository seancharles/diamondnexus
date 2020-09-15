<?php

namespace ForeverCompanies\QuoteCleaner\Cron;

use Magento\Framework\Stdlib\DateTime\DateTime;
use Psr\Log\LoggerInterface;

/**
 * Cleaner cron class
 */
class Cleaner
{
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var \ForeverCompanies\QuoteCleaner\Helper\Cleaner
     */
    protected $cleanerHelper;

    /**
     * @var DateTime
     */
    protected $dateTime;

    /**
     * Cleaner constructor.
     * @param LoggerInterface $logger
     * @param \ForeverCompanies\QuoteCleaner\Helper\Cleaner $cleanerHelper
     * @param DateTime $dateTime
     */
    public function __construct(
        LoggerInterface $logger,
        \ForeverCompanies\QuoteCleaner\Helper\Cleaner $cleanerHelper,
        DateTime $dateTime
    ) {
        $this->logger = $logger;
        $this->cleanerHelper = $cleanerHelper;
        $this->dateTime = $dateTime;
    }

    /**
     * Execute the cron
     * @return void
     */
    public function execute()
    {
        if ($this->cleanerHelper->getCron()) {
            $this->logger->addInfo((string) __('[%1] Cleaner Cronjob Start', $this->dateTime->gmtDate()));
            $this->logger->addInfo('Cleaning customer quotes');
            $result = $this->cleanerHelper->cleanCustomerQuotes();
            $this->logger->addInfo(
                (string) __(
                    'Result: in %1 cleaned %2 customer quotes',
                    $result['quote_duration'],
                    $result['quote_count']
                )
            );
            $this->logger->addInfo('Cleaning anonymous quotes');
            $result = $this->cleanerHelper->cleanAnonymousQuotes();
            $this->logger->addInfo(
                (string) __(
                    'Result: in %1 cleaned %2 anonymous quotes',
                    $result['quote_duration'],
                    $result['quote_count']
                )
            );
            $this->logger->addInfo((string) __('[%1] Cleaner Cronjob Finish', $this->dateTime->gmtDate()));
        }
    }
}
