<?php

namespace ForeverCompanies\SearchEngine\Setup\Patch\Data;

use Magento\Config\Model\ResourceModel\Config as ResourceConfig;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class AddElasticsearchConfig implements DataPatchInterface
{
    /**
     * @var ResourceConfig
     */
    protected $resourceConfig;

    /**
     * @var ModuleDataSetupInterface
     */
    protected $moduleDataSetup;

    /**
     * @var array
     */
    protected $configs = [
        [
            'path' => \Magento\Config\Model\Config\Backend\Admin\Custom::XML_PATH_CATALOG_SEARCH_ENGINE,
            'value' => 'elasticsearch6'
        ],[
            'path' => \Magento\Catalog\Model\Indexer\Product\Eav\AbstractAction::ENABLE_EAV_INDEXER,
            'value' => '1'
        ],[
            'path' => 'catalog/search/elasticsearch6_server_hostname',
            'value' => 'elasticsearch'
        ],[
            'path' => 'catalog/search/elasticsearch6_server_port',
            'value' => '9200'
        ],[
            'path' => 'catalog/search/elasticsearch6_index_prefix',
            'value' => 'magento2'
        ],[
            'path' => 'catalog/search/elasticsearch6_enable_auth',
            'value' => '0'
        ],[
            'path' => 'catalog/search/elasticsearch6_server_timeout',
            'value' => '15'
        ],[
            'path' => \Magento\AdvancedSearch\Model\SuggestedQueriesInterface::SEARCH_RECOMMENDATIONS_ENABLED,
            'value' => '0'
        ],
    ];

    /**
     * @param ResourceConfig $resourceConfig
     * @param ModuleDataSetupInterface $moduleDataSetup
     */
    public function __construct(
        ResourceConfig $resourceConfig,
        ModuleDataSetupInterface $moduleDataSetup
    ) {
        $this->resourceConfig = $resourceConfig;
        $this->moduleDataSetup = $moduleDataSetup;
    }

    /**
     * {@inheritdoc}
     * @throws \Throwable
     */
    public function apply()
    {
        $this->moduleDataSetup->getConnection()->startSetup();
        $this->addElasticsearchConfig();
        $this->moduleDataSetup->getConnection()->endSetup();
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }

    public static function getVersion()
    {
        return '0.0.1';
    }

    /**
     * Change search engine to elasticsearch
     */
    protected function addElasticsearchConfig()
    {
        foreach ($this->configs as $config) {
            $this->resourceConfig->saveConfig($config['path'], $config['value']);
        }
    }
}
