<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace ForeverCompanies\Salesforce\Model\Sync;

use ForeverCompanies\Salesforce\Model\RequestLogFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use ForeverCompanies\Salesforce\Model\BulkConnector;


class Job extends BulkConnector
{
    /**
     * @var string
     */
    protected $jobId;

    /**
     * @var string
     */
    protected $batchId;

    public function __construct(ScopeConfigInterface $scopeConfig, RequestLogFactory $requestLogFactory)
    {
        parent::__construct($scopeConfig, $requestLogFactory);
    }

    /**
     * @param string $operation
     * @param string $object
     * @param string $batch
     * @param string $contentType
     * @return mixed|string
     */
    public function sendBatchRequest($operation = '',
                                     $object = '', $batch = '', $contentType = 'JSON')
    {
        try {
           if ($batch == '[]'){
               return 'Batch is empty';
           }
           $batchResultId = '';
           $this->getAccessToken();
           $this->createJob($operation, $object, $contentType);
           $queryResult = $this->getBatchResult($batchResultId);
           $this->closeJob();
           return $queryResult;
           $this->getAccessToken();
        } catch (\Exception $e) {
            echo $e->getMessage();
            return $e->getMessage();
        }
    }

    protected function getBatchResult($resultId = '')
    {
        do {
          $url = $this->scopeConfig->getValue(
              self::XML_PATH_SALESFORCE_INSTANCE_URL) . '/services/async/38.0/job/'.
              $this->jobId . '/batch/' . $this->batchId . '/result/' . $resultId;
          $headers = [
              'Content-Type' => 'application/json',
              'charset' => 'UTF-8',
              'X-SFDC-Session' => $this->sessionId
          ];
          $response = $this->sendRequest($url, \Zend_Http_Client::GET, $headers);
          $decodedResponse = json_decode($response, true);
        } while (isset($decodedResponse['exceptionMessage']) && $decodedResponse['exceptionMessage'] == 'Batch not completed');
        return $decodedResponse;
    }
}
