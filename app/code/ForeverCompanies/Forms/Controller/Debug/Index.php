<?php
namespace ForeverCompanies\Forms\Controller\Debug;
class Index extends \ForeverCompanies\Forms\Controller\ApiController
{
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        parent::__construct($context);
        $this->storeManager = $storeManager;
    }
    public function execute()
    {
        echo time();
        echo "<br>TESTING :(<br>";
        echo "HODL!!!<br>";
        //$url = parse_url($this->storeManager->getStore()->getBaseUrl());
        //echo $url['subdomain'] . "<br />";
        //echo $url['domain'] . "<br />";
        //echo $url['extension'] . "<br />";
    }
}