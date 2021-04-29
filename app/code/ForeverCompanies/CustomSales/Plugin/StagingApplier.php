<?php

namespace ForeverCompanies\CustomSales\Plugin;

class StagingApplier
{
	protected $logger;
	
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
		\Magento\Staging\Api\UpdateRepositoryInterface $updateRepository,
		\Magento\Staging\Model\VersionHistoryInterface $versionHistory,
        \Magento\Framework\App\ResourceConnection $resourceConnection
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
            
            if(isset($websiteList) === true) {
                
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
                    $request->setUri('http://' . $_ENV['MAG_NAME'] . '-pro.1215diamonds.com?');
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
                    
                    // htauth
                    $client->setAuth('diamondnexus', 'L2gchVekvtjY4nvF');

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