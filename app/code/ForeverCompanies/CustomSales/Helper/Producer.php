<?php

namespace ForeverCompanies\CustomSales\Helper;

use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\Helper\AbstractHelper;
use ForeverCompanies\CustomSales\Model\Producer\Constant;

class Producer extends AbstractHelper
{
    protected $logger;
    protected $updateRepository;
    protected $versionHistory;
    protected $resourceConnection;
    protected $scopeConfig;

    protected $isProducerEnabled;
    protected $useSSL;
    protected $producerHost;
    protected $useBasicAuth;
    protected $authUser;
    protected $authPass;

    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \Magento\Staging\Api\UpdateRepositoryInterface $updateRepository,
        \Magento\Staging\Model\VersionHistoryInterface $versionHistory,
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        Context $context
    ) {
        parent::__construct($context);

        $this->logger = $logger;
        $this->updateRepository = $updateRepository;
        $this->versionHistory = $versionHistory;
        $this->resourceConnection = $resourceConnection;
        $this->scopeConfig = $scopeConfig;

        $this->getConfig();
    }

    protected function getConfig() {
        $this->isProducerEnabled = $this->scopeConfig->getValue(Constant::PRODUCER_CONNECTION_ENABLED);
        $this->useSSL = $this->scopeConfig->getValue(Constant::PRODUCER_CONNECTION_USE_SSL);
        $this->producerHost = $this->scopeConfig->getValue(Constant::PRODUCER_CONNECTION_HOST);

        $this->useBasicAuth = $this->scopeConfig->getValue(Constant::PRODUCER_AUTH_ENABLED);
        $this->authUser = $this->scopeConfig->getValue(Constant::PRODUCER_AUTH_USER);
        $this->authPass = $this->scopeConfig->getValue(Constant::PRODUCER_AUTH_PASS);
    }

    public function exportElder() {
        $connection = $this->resourceConnection->getConnection();

        $query = "SELECT
                        value
                    FROM
                        core_config_data
                    WHERE
                        path = '" . Constant::PRODUCER_VERSION . "';";

        $row = $connection->fetchRow($query);
        $lastVersion = $row['value'];

        $siteCodes = [
            1 => 'dn',
            2 => 'fa',
            3 => 'tf',
            4 => 'fc'
        ];

        $versionId = $this->versionHistory->getCurrentId();

        $this->logger->debug("staging applier: version = " . $versionId . ", lastVersion=" . $lastVersion);

        if ($versionId > 0 && $lastVersion != $versionId) {
            $version = $this->updateRepository->get($versionId);
            $versionTitle = $version->getName();

            $this->logger->debug("staging applier: applying update " . $versionTitle);

            $query = "UPDATE
                            core_config_data
                        SET
                            value = '" . filter_var($versionId, FILTER_SANITIZE_SPECIAL_CHARS) . "'
                        WHERE
                            path = '" . Constant::PRODUCER_VERSION . "';";
            $connection->query($query);

            $query = "SELECT
                        crw.website_id
                    FROM
                        staging_update su
                    INNER JOIN
                        catalogrule cr ON su.id = cr.created_in
                    INNER JOIN
                        catalogrule_website crw ON cr.row_id = crw.row_id
                    WHERE
                        su.id = '" . filter_var($versionId, FILTER_SANITIZE_SPECIAL_CHARS) . "';";

            $websiteList = $connection->fetchAll($query);

            if ($this->isProducerEnabled && strlen($this->producerHost) > 0 && isset($websiteList) === true) {
                foreach ($websiteList as $row) {
                    $websiteCode = $siteCodes[$row['website_id']];
                    $this->logger->debug("Running elder build for: " . $websiteCode);

                    $response = $this->sendProducerCall('exportelder--' . $websiteCode);

                    if($response === true) {
                        $this->logger->debug("Staging applier: applied sale: " . $versionTitle);
                    } else {
                        $this->logger->debug("Staging applier failed: response code: " . $response);
                    }
                }
            }

            $this->logger->debug("Staging Applier Plugin Completed");
        }
    }

    public function flushElderCache() {
        $flushTask = "{%22siteId%22:%22api%22,%22cmd%22:%22cacheclear%22,%22key%22:%22key0.2311580663534265%22,%22page%22:1,%22totalPages%22:1,%22data%22:[%22/*%22]}";
        $response = $this->sendProducerCall($flushTask);

        if($response === true) {
            $this->logger->debug("Elder flush: complete");
        } else {
            $this->logger->debug("Elder flush: failed, response code: " . $response);
        }
    }

    protected function sendProducerCall($task = null) {
        try {
            $params = new \Zend\Stdlib\Parameters([
                'task' => $task,
                'source' => 'mag'
            ]);

            $httpHeaders = new \Zend\Http\Headers();
            $httpHeaders->addHeaders([
                'Accept' => 'text/html',
                'Content-Type' => 'text/html'
            ]);

            $request = new \Zend\Http\Request();
            $request->setHeaders($httpHeaders);
            $request->setUri((($this->useSSL == 1) ? 'https://' : 'http://') . $this->producerHost);
            $request->setMethod(\Zend\Http\Request::METHOD_GET);
            $request->setQuery($params);

            $client = new \Zend\Http\Client();
            $options = [
                'adapter' => 'Zend\Http\Client\Adapter\Curl',
                'curloptions' => [CURLOPT_FOLLOWLOCATION => true],
                'maxredirects' => 1,
                'timeout' => 30
            ];
            $client->setOptions($options);
            // conditionally use basic auth
            if ($this->useBasicAuth) {
                $client->setAuth($this->authUser, $this->authPass);
            }
            $response = $client->send($request);

            if($response->getStatusCode() != 200) {
                return $response->getStatusCode();
            } else {
                return true;
            }

        } catch (\Exception $e) {
            $this->logger->critical('Producer exception:', ['exception' => $e]);
        }
    }

    public function isAllowed()
    {
        return $this->session->isAllowed('ForeverCompanies_CustomSales::shipdate_config');
    }
}
