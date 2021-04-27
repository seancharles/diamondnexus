<?php

namespace ForeverCompanies\CustomSales\Plugin;

class StagingApplier
{
	protected $logger;
	
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
		\Magento\Staging\Api\UpdateRepositoryInterface $updateRepository,
		\Magento\Staging\Model\VersionHistoryInterface $versionHistory
    ){
        $this->logger = $logger;
		$this->updateRepository = $updateRepository;
		$this->versionHistory = $versionHistory;
    }
	
    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterExecute($subject, $result) {
        
		$versionId = $this->versionHistory->getCurrentId();
		
		if($versionId > 0) {
			$version = $this->updateRepository->get($versionId);

			$versionTime = $version->getStartTime();
			$versionTitle = $version->getName();

			$this->logger->debug($versionTime);
			$this->logger->debug($versionTitle);
		
			$this->logger->debug("Staging Applier Plugin Completed");
            
            $params = new \Zend\Stdlib\Parameters([
                'task' => 'exportelder--dn',
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
		
		return $result;
    }
}