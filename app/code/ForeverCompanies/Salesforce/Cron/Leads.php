<?php
namespace ForeverCompanies\Salesforce\Cron;

class Leads
{
    protected $sync;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Demo\HelloWorld\Model\Customer $customer
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \ForeverCompanies\Salesforce\Helper\Sync $syncHelper
    ) {
        $this->syncHelper = $syncHelper;
    }
    
    public function execute()
    {
        $this->syncHelper->runLeads();

        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/cron.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        $logger->info(__METHOD__);

        return $this;

    }
}
