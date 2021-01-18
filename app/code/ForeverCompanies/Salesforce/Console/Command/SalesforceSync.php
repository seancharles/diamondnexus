<?php
    namespace ForeverCompanies\Salesforce\Console\Command;
    
    use Symfony\Component\Console\Command\Command;
    use Symfony\Component\Console\Input\InputInterface;
    use Symfony\Component\Console\Input\InputOption;
    use Symfony\Component\Console\Output\OutputInterface;

    /**
     * Class SomeCommand
     */
    class SalesforceSync extends Command
    {
        /**
         * @var string
         */
        protected $name = 'forevercompanies:salesforce:sync';

        /**
         * SalesforceSync constructor.
         * @param \ForeverCompanies\Salesforce\Helper\Sync$syncHelper
         */

        public function __construct(
            \ForeverCompanies\Salesforce\Helper\Sync $syncHelper
        ) {
            $this->syncHelper = $syncHelper;
            
            parent::__construct($this->name);
        }

        /**
         * {@inheritdoc}
         */
        protected function configure()
        {
            $this->setName($this->name);
            $this->setDescription("Sync magento orders/customers to Salesforce");
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
            $this->syncHelper->run();
        }
    }