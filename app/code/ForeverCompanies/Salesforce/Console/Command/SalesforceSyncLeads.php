<?php
    namespace ForeverCompanies\Salesforce\Console\Command;
    
    use Magento\Framework\App\Area;
    use Symfony\Component\Console\Command\Command;
    use Symfony\Component\Console\Input\InputInterface;
    use Symfony\Component\Console\Input\InputOption;
    use Symfony\Component\Console\Output\OutputInterface;

    /**
     * Class SomeCommand
     */
    class SalesforceSyncLeads extends Command
    {
        /**
         * @var string
         */
        protected $name = 'forevercompanies:salesforce:leads:sync';

        protected $syncHelper;
        protected $state;

        /**
         * SalesforceSync constructor.
         * @param \ForeverCompanies\Salesforce\Helper\Sync $syncHelper
         */

        public function __construct(
            \ForeverCompanies\Salesforce\Helper\Sync $syncHelper,
            \Magento\Framework\App\State $state
        ) {
            $this->syncHelper = $syncHelper;
            $this->state = $state;
            
            parent::__construct($this->name);
        }

        /**
         * {@inheritdoc}
         */
        protected function configure()
        {
            $this->setName($this->name);
            $this->setDescription("Sync magento Leads to Salesforce");
            parent::configure();
        }

        /**
         * Execute the command
         *
         * @param InputInterface $input
         * @param OutputInterface $output
         *
         * @return null|int
         * @throws \Magento\Framework\Exception\LocalizedException
         */
        protected function execute(InputInterface $input, OutputInterface $output)
        {
            $this->state->setAreaCode(\Magento\Framework\App\Area::AREA_ADMINHTML);
            $this->syncHelper->runLeads();
        }
    }
