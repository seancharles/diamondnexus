<?php
namespace ForeverCompanies\LooseStoneImport\Model;

use Magento\Framework\File\Csv;
use Magento\Catalog\Model\Product\Action as ProductAction;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Catalog\Model\Product;

class StoneDisable
{
    protected $csv;
    protected $fileName;
    protected $productAction;
    protected $statusDisabled;
    protected $productCollection;
    protected $productModel;
    
    public function __construct(
        Csv $cs,
        ProductAction $action,
        CollectionFactory $collection,
        Product $prod
    ) {
        $this->csv = $cs;
        $this->productAction = $action;
        $this->productCollection = $collection;
        $this->productModel = $prod;
        
        $this->statusDisabled = \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_DISABLED;;
        $this->fileName = '/var/www/magento/var/import/disable_stones.csv';
    }
    
    function run()
    {
        
        $skuArr = $this->buildArray();
        
        var_dump($skuArr);
        echo 'the sku arr count is ' . count($skuArr) . '   ';
        
        $count = 0;
        foreach ($skuArr as $sku) {
            $productId = $this->productModel->getIdBySku($sku);
            if ($productId) {
                $product = $this->productModel->load($productId);
                $product->setStatus($this->statusDisabled);
                $product->save();
                $count++;
            }
        }
        echo 'complete. the product count is ' . $count . '   ';
    }
    
    public function buildArray()
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
                if (strtolower($data[1]) == 'disabled') {
                    $arr[] = trim($data[0]);
                    continue;
                }
            }
        }
        return $arr;
    }
}