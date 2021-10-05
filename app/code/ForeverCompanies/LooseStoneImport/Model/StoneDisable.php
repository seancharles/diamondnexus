<?php

namespace ForeverCompanies\LooseStoneImport\Model;

use Exception;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Framework\File\Csv;
use Magento\Catalog\Model\ResourceModel\Product\Action as ProductAction;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Catalog\Model\Product;
use Magento\Framework\App\Config\ScopeConfigInterface;

class StoneDisable
{
    protected Csv $csv;
    protected ProductAction $productAction;
    protected CollectionFactory $productCollection;
    protected Product $productModel;
    protected int $statusDisabled;
    protected string $fileName;
    protected $scopeConfig;
    protected $storeScope;

    public function __construct(
        Csv $cs,
        ProductAction $action,
        CollectionFactory $collection,
        Product $prod,
        ScopeConfigInterface $scopeC
    ) {
        $this->csv = $cs;
        $this->productAction = $action;
        $this->productCollection = $collection;
        $this->productModel = $prod;
        $this->statusDisabled = Status::STATUS_DISABLED;
        $this->fileName = '/var/www/magento/var/import/disable_stones.csv';
        $this->scopeConfig = $scopeC;
        $this->storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
    }

    /**
     * Execute script - disable all stones listed in given file
     * @throws Exception
     */
    public function run()
    {
        $this->updateDisableStonesCsv();
        $skuArr = $this->buildArray();

        $count = 0;
        $productIds = [];
        foreach ($skuArr as $sku) {
            $productId = $this->productModel->getIdBySku($sku);
            if ($productId) {
                $productIds[] = $productId;
                $count++;
            }
        }

        if (sizeof($productIds) > 0) {
            try {
                $this->productAction->updateAttributes(
                    $productIds,
                    ['status' => Status::STATUS_DISABLED],
                    0
                );
            } catch (Exception $e) {
                echo "Error: " . $e->getMessage() . "\n";
            }
        }
        echo 'complete. the product count is ' . $count . '   ';
    }

    /**
     * Build array of data to be processed, from the file defined at $this->fileName
     * @return array
     * @throws Exception
     */
    public function buildArray(): array
    {
        $arr = array();
        $fields = array();
        $i = 0;

        if (file_exists($this->fileName)) {
            $csvData = $this->csv->getData($this->fileName);
            foreach ($csvData as $data) {
                if ($i == 0) {
                    $i++;
                    continue;
                }
                $arr[] = trim($data[0]);
            }
        }
        return $arr;
    }

    /**
     * Connect to FTP server and pull down latest disable stones sheet
     */
    protected function updateDisableStonesCsv()
    {
        $ftp = ftp_connect(
            $this->scopeConfig->getValue('forevercompanies_stone_ftp/creds/host', $this->storeScope),
            $this->scopeConfig->getValue('forevercompanies_stone_ftp/creds/port', $this->storeScope)
        );

        $login_result = ftp_login(
            $ftp,
            $this->scopeConfig->getValue('forevercompanies_stone_ftp/creds/user', $this->storeScope),
            $this->scopeConfig->getValue('forevercompanies_stone_ftp/creds/pass', $this->storeScope)
        );
        ftp_pasv($ftp, true);

        $files = ftp_nlist(
            $ftp,
            ftp_pwd($ftp) . DS . $this->scopeConfig->getValue(
                'forevercompanies_stone_ftp/creds/disable_pattern',
                $this->storeScope
            )
        );

        foreach ($files as $file) {
            ftp_get($ftp, '/var/www/magento/var/import/disable_stones.csv', $file);
        }

        ftp_close($ftp);
    }
}
