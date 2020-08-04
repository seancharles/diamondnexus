<?php

/**
 * Copyright (c) 2018, Prog Leasing, LLC
 * Licensed under the Open Software License version 3.0
 */

namespace Progressive\PayWithProgressive\Console;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Command\Command;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Sales\Setup\SalesSetupFactory;
use Magento\Quote\Setup\QuoteSetupFactory;

use Progressive\PayWithProgressive\Setup\InstallData;


/**
 * Class AddLeasableAttribute
 * @package Progressive\PayWithProgressive\Console
 */
class AddLeasableAttribute extends Command
{
    public $helper;
    protected $_eavSetupFactory;
    protected $_quoteSetupFactory;
    protected $_salesSetupFactory;

    /**
     * AddLeasableAttribute constructor.
     * @param InstallData $installData
     * @param EavSetupFactory $eavSetupFactory
     */
    public function __construct(
        InstallData $installData,
	EavSetupFactory $eavSetupFactory,
	QuoteSetupFactory $quoteSetupFactory,
	SalesSetupFactory $salesSetupFactory
    )
    {
        $this->helper = $installData;
	$this->_eavSetupFactory = $eavSetupFactory;
	$this->_quoteSetupFactory = $quoteSetupFactory;
	$this->_salesSetupFactory = $salesSetupFactory;

        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('progressive:addattribute');
        $this->setDescription('Add PayWithProgressive leasable attributes');

        parent::configure();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $installData = new InstallData($this->_eavSetupFactory, $this->_quoteSetupFactory, $this->_salesSetupFactory);
        $installData->install($setup, $context);
    }

}
