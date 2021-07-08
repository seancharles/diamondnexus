<?php

namespace ForeverCompanies\CustomSales\Plugin;

class StagingApplier
{
	protected $logger;
    protected $updateRepository;
    protected $versionHistory;
    protected $resourceConnection;
    protected $scopeConfig;
	
    const PRODUCER_CONNECTION_ENABLED   = 'forevercompanies_producer/connection/enabled';
    const PRODUCER_CONNECTION_USE_SSL   = 'forevercompanies_producer/connection/use_ssl';
    const PRODUCER_CONNECTION_HOST      = 'forevercompanies_producer/connection/host';
    
    const PRODUCER_AUTH_ENABLED   = 'forevercompanies_producer/basic_auth/enabled';
    const PRODUCER_AUTH_USER      = 'forevercompanies_producer/basic_auth/user';
    const PRODUCER_AUTH_PASS      = 'forevercompanies_producer/basic_auth/pass';
    
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
		\Magento\Staging\Api\UpdateRepositoryInterface $updateRepository,
		\Magento\Staging\Model\VersionHistoryInterface $versionHistory,
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ){
        $this->logger = $logger;
		$this->updateRepository = $updateRepository;
		$this->versionHistory = $versionHistory;
        $this->resourceConnection = $resourceConnection;
    }
	
    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterExecute($subject, $result) {
        
        $isProducerEnabled = $this->scopeConfig->getValue(self::PRODUCER_CONNECTION_ENABLED);
        $useSSL = $this->scopeConfig->getValue(self::PRODUCER_CONNECTION_USE_SSL);
        $producerHost = $this->scopeConfig->getValue(self::PRODUCER_CONNECTION_HOST);
        
        $useBasicAuth = $this->scopeConfig->getValue(self::PRODUCER_AUTH_ENABLED);
        $authUser = $this->scopeConfig->getValue(self::PRODUCER_AUTH_USER);
        $authPass = $this->scopeConfig->getValue(self::PRODUCER_AUTH_PASS);
        
        $siteCodes = [
            1 => 'dn',
            2 => 'fa',
            3 => 'tf',
            4 => 'fc'
        ];
        
		$versionId = $this->versionHistory->getCurrentId();
		
        $this->logger->debug("staging applier: version = " . $versionId);
        
		if($versionId > 0) {
			$version = $this->updateRepository->get($versionId);

			$versionTime = $version->getStartTime();
			$versionTitle = $version->getName();

            $connection = $this->resourceConnection->getConnection();
            
            $query = "SELECT
                        crw.website_id
                    FROM
                        staging_update su
                    INNER JOIN
                        catalogrule cr ON su.id = cr.created_in
                    INNER JOIN
                        catalogrule_website crw ON cr.row_id = crw.row_id
                    WHERE
                        su.id = '" . $versionId . "';";
            
            $websiteList = $connection->fetchAll($query);
            
            if($isProducerEnabled && strlen($producerHost) > 0 && isset($websiteList) === true) {
                
                foreach($websiteList as $row) {
                    $websiteCode = $siteCodes[$row['website_id']];
                    
                    $this->logger->debug("Running elder build for: " . $websiteCode);
                    
                    $params = new \Zend\Stdlib\Parameters([
                        'task' => 'exportelder--' . $websiteCode,
                        'source' => 'mag'
                    ]);
                    
                    $httpHeaders = new \Zend\Http\Headers();
                    $httpHeaders->addHeaders([
                       'Accept' => 'text/html',
                       'Content-Type' => 'text/html'
                    ]);
                    
                    $request = new \Zend\Http\Request();
                    $request->setHeaders($httpHeaders);
                    $request->setUri( (($useSSL == 1) ? 'https://' : 'http://' ) . $producerHost);
                    $request->setMethod(\Zend\Http\Request::METHOD_GET);
                    $request->setQuery($params);
                    
                    $client = new \Zend\Http\Client();
                    $options = [
                        'adapter'   => 'Zend\Http\Client\Adapter\Curl',
                        'curloptions' => [CURLOPT_FOLLOWLOCATION => true],
                        'maxredirects' => 1,
                        'timeout' => 30
                    ];
                    $client->setOptions($options);
                    
                    // conditionally use basic auth
                    if($useBasicAuth) {
                        $client->setAuth($authUser, $authPass);
                    }

                    $response = $client->send($request);
                }
            }

			$this->logger->debug($versionTime);
			$this->logger->debug($versionTitle);
		
			$this->logger->debug("Staging Applier Plugin Completed");
		}
		
		return $result;
    }
}