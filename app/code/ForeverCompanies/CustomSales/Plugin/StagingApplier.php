<?php

namespace ForeverCompanies\CustomSales\Plugin;

class StagingApplier
{
	protected $logger;
	
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
		\Magento\Staging\Api\UpdateRepositoryInterface $updateRepository,
		\Magento\Staging\Model\VersionHistoryInterface $versionHistory,
        \Magento\Framework\Mail\Template\TransportBuilder,
        \Magento\Framework\App\Helper\AbstractHelper,
        \Magento\Framework\Translate\Inline\StateInterface,
        \Magento\Store\Model\StoreManagerInterface,
        \GuzzleHttp\Client,
        \GuzzleHttp\ClientFactory,
        \GuzzleHttp\Exception\GuzzleException,
        \GuzzleHttp\Psr7\Response,
        \GuzzleHttp\Psr7\ResponseFactory,
        \Magento\Framework\Webapi\Rest\Request
    ){
        $this->logger = $logger;
		$this->updateRepository = $updateRepository;
		$this->versionHistory = $versionHistory;
        $this->transportBuilder = $transportBuilder;
        $this->storeManager = $storeManager;
        $this->inlineTranslation = $state;
        $this->clientFactory = $clientFactory;
        $this->responseFactory = $responseFactory;
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
            
            $client = $this->clientFactory->create(['config' => [
                'base_uri' => 'http://' . $_ENV['MAG_NAME'] . '-pro.1215diamonds.com'
            ]]);
            
            try {
                $response = $client->request(
                    "GET",
                    "/",
                    [
                        'auth' => ['diamondnexus', 'L2gchVekvtjY4nvF'],
                        'task' => 'export--dn',
                        'source' => 'mag'
                    ]
                );
            } catch (GuzzleException $exception) {
                /** @var Response $response */
                $response = $this->responseFactory->create([
                    'status' => $exception->getCode(),
                    'reason' => $exception->getMessage()
                ]);
                
                // TODO: Add handling for JSON response with email notification if response is not successfull
                
                //if($response->getSuccess() != 'true') {
                //    $this->sendEmail();
                //}
            }
		}
		
		return $result;
    }
    
    protected function sendEmail()
    {
        $templateId = 'my_custom_email_template';
        $fromEmail = 'it@forevercompanies.com';
        $fromName = 'Admin';
        $toEmail = 'it@forevercompanies.com';
 
        try {
            // template variables pass here
            $templateVars = [
                'msg' => 'Scheduler sapper build automation error'
            ];
 
            $storeId = $this->storeManager->getStore()->getId();
 
            $from = ['email' => $fromEmail, 'name' => $fromName];
            $this->inlineTranslation->suspend();
 
            $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
            $templateOptions = [
                'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                'store' => $storeId
            ];
            $transport = $this->transportBuilder->setTemplateIdentifier($templateId, $storeScope)
                ->setTemplateOptions($templateOptions)
                ->setTemplateVars($templateVars)
                ->setFrom($from)
                ->addTo($toEmail)
                ->getTransport();
            $transport->sendMessage();
            $this->inlineTranslation->resume();
        } catch (\Exception $e) {
            $this->_logger->info($e->getMessage());
        }
    }
}