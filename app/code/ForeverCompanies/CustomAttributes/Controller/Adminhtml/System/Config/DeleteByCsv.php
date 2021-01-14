<?php

namespace ForeverCompanies\CustomAttributes\Controller\Adminhtml\System\Config;

use ForeverCompanies\CustomAttributes\Logger\Logger;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use ForeverCompanies\CustomAttributes\Helper\TransformData;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\StateException;
use Magento\Catalog\Model\ProductRepository;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\File\Csv;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\Message\ManagerInterface;
use Magento\Store\Model\StoreManagerInterface;

class DeleteByCsv extends Action
{

    /**
     * @var TransformData
     */
    protected $helper;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var ManagerInterface
     */
    protected $messageManager;

    /**
     * @var Csv
     */
    protected $csv;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var ProductRepository
     */
    protected $productRepository;

    /**
     * @var File
     */
    protected $fileSystem;

    /**
     * DeleteByCsv constructor.
     * @param Context $context
     * @param ManagerInterface $messageManager
     * @param Csv $csv
     * @param StoreManagerInterface $storeManager
     * @param ScopeConfigInterface $scopeConfig
     * @param ProductRepository $productRepository
     * @param Logger $logger
     * @param File $fileSystem
     */
    public function __construct(
        Context $context,
        ManagerInterface $messageManager,
        Csv $csv,
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig,
        ProductRepository $productRepository,
        Logger $logger,
        File $fileSystem
    ) {
        parent::__construct($context);
        $this->messageManager = $messageManager;
        $this->csv = $csv;
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
        $this->productRepository = $productRepository;
        $this->logger = $logger;
        $this->fileSystem = $fileSystem;
    }

    /**
     * Collect relations data
     *
     * @return void
     * @throws StateException|NoSuchEntityException
     * @throws FileSystemException
     */
    public function execute()
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORES;
        $urlTypeMedia = \Magento\Framework\UrlInterface::URL_TYPE_MEDIA;
        $scopeConfig = 'forevercompanies_customattributes/general/forevercompanies_deletebycsv_file';
        $uploadedCsvFilePath = $this->scopeConfig->getValue($scopeConfig, $storeScope);
        $pubMediaUrl = $this->storeManager->getStore()->getBaseUrl($urlTypeMedia);
        $uploadedCsv = $pubMediaUrl . "forevercompanies/" . $uploadedCsvFilePath;

        if ($uploadedCsvFilePath != '') {
            $handle = $this->fileSystem->fileOpen($uploadedCsv, 'r');

            $this->fileSystem->fileGetCsv($handle);

            while ($row = $this->fileSystem->fileGetCsv($handle)) {
                $productId = $row[0];
                $type = $row[1];
                $keep = $row[2];
                $sku = $row[3];
                if ($keep == 'NO') {
                    try {
                        $product = $this->productRepository->getById($productId);
                        if ($product->getTypeId() == $type && $product->getSku() == $sku) {
                            $this->productRepository->delete($product);
                        }
                    } catch (NoSuchEntityException $e) {
                        $this->logger->info('Product ID = ' . $productId . ' not found');
                        continue;
                    }
                }
            }
        }
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('ForeverCompanies_CustomAttributes::config');
    }
}
