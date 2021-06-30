<?php

namespace ForeverCompanies\CostImport\Model\Config\Backend;

use Magento\Config\Model\Config\Backend\File;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Message\ManagerInterface;
use Magento\Catalog\Model\ProductFactory;
use Magento\Framework\Filesystem;

class CustomFileType extends File
{
    protected $connection;
    protected $messageManager;
    protected $productFactory;
    
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \Magento\MediaStorage\Model\File\UploaderFactory $uploaderFactory,
        \Magento\Config\Model\Config\Backend\File\RequestData\RequestDataInterface $requestData,
        Filesystem $filesystem,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        ResourceConnection $resourceC,
        ManagerInterface $managerI,
        ProductFactory $productF,
        array $data = []
        ) { 
            $this->connection = $resourceC->getConnection();
            $this->messageManager = $managerI;
            $this->productFactory = $productF;
            
            parent::__construct(
                $context,
                $registry,
                $config,
                $cacheTypeList,
                $uploaderFactory,
                $requestData,
                $filesystem,
                $resource,
                $resourceCollection,
                $data
            );
    }
    
    public function beforeSave()
    {   
        ini_set('auto_detect_line_endings',TRUE);
        
        $headerErrors = [];
        $products = [];
        $skus = [];
        $rowsUpdated = 0;
        $line = 1;
        
        $value = $this->getValue();
        $file = $this->getFileData();
        if (!empty($file)) 
        {   
            $uploadDir = $this->_getUploadDir();
            try {
                /** @var Uploader $uploader */
                $uploader = $this->_uploaderFactory->create(['fileId' => $file]);
                $uploader->setAllowedExtensions($this->_getAllowedExtensions());
                $uploader->setAllowRenameFiles(true);
                $uploader->addValidateCallback('size', $this, 'validateMaxSize');
                $result = $uploader->save($uploadDir);
            } catch (\Exception $e) {
                throw new \Magento\Framework\Exception\LocalizedException(__('%1', $e->getMessage()));
            }

            $filename = $result['file'];
            if ($filename) {
                if ($this->_addWhetherScopeInfo()) {
                    $filename = $this->_prependScopeInfo($filename);
                }
                $this->setValue($filename);
            }
        } else {
            if (is_array($value) && !empty($value['delete'])) {
                $this->setValue('');
            } elseif (is_array($value) && !empty($value['value'])) {
                $this->setValue($value['value']);
            } else {
                $this->unsValue();
            }
        }
        
        if (1==1) {
            
        $fp = fopen($result['path'] . DS . $result['file'], "r");
        
        if( ($headers = fgetcsv($fp) ) !== FALSE ) {
            if(strtolower($headers[0]) != "sku") {
                $headerErrors[] = "Missing column header sku";
            }
            if(strtolower($headers[1]) != "cost") {
                $headerErrors[] = "Missing column header cost";
            }
            if(strtolower($headers[2]) != "price") {
                $headerErrors[] = "Missing column header price";
            }
            if($headerErrors) {
                foreach($headerErrors as $error) {
                    $this->messageManager->addError($error);
                }
            } else {
                
                while ( ($data = fgetcsv($fp) ) !== FALSE ) {
                    $line++;
                    $errors = [];
                    
                    if(!strlen($data[0]) > 0) {
                        $errors[] = "Invalid Sku on line: " . $line;
                    }
                    
                    if(!$data[1] > 0 && !$data[2] > 0) {
                        $errors[] = "Invalid Price & Cost on line: " . $line;
                    }
                    
                    if($errors) {
                        foreach($errors as $error) {
                            $this->messageManager->addError($error);
                        }
                    } else {
                        $skus[] = $data[0];
                        
                        $products[$data[0]] = [
                            'cost' => $data[1],
                            'price' => $data[2]
                        ];
                    }
                }
                
                $this->messageManager->addSuccess("Products processed: " . $line);
                
                $sql = "SELECT
							e.entity_id,
							e.sku,
							i.qty,
							p.value supplier
						FROM
							catalog_product_entity e
						INNER JOIN
							cataloginventory_stock_item i ON e.entity_id = i.product_id
						INNER JOIN
							catalog_product_entity_int p ON (e.entity_id = p.row_id AND p.attribute_id = 344)
						WHERE
							i.qty > 0
						AND
							e.sku IN('" . implode("','", $skus) . "');";
                
                $result = $this->connection->fetchAll($sql);
                
                // add the product id to each product
                foreach($result as $res) {
                    $products[$res['sku']]['id'] = $res['entity_id'];
                    $products[$res['sku']]['supplier'] = $res['supplier'];
                }
                
                foreach($products as $sku => $product) {
                  
                    if(!$product['id'] > 0) {
                        $this->messageManager->addError("unable to find sku: " . $sku);
                    } else {
                        $modifyThisProduct = $this->productFactory->create()->load($product['id']);
                        
                        if( $product['supplier'] == "34" || $product['supplier'] == "36" ) {
                            // update stones intermediary
                            $this->connection->query("UPDATE stones_intermediary SET final_cost = '" . $product['cost'] . "' WHERE certificate_number = '" . $sku . "';");
                            
                            $modifyThisProduct->setStoneImportPriceOverride(($product['price'] > 0) ? '1' : '0');
                            $modifyThisProduct->setPrice($product['price']);
                            $modifyThisProduct->setStoneImportCostOverride(($product['cost'] > 0) ? '1' : '0');
                            $modifyThisProduct->setStoneImportCustomCost($product['cost']);
                        } else {
                            $modifyThisProduct->setStoneImportPriceOverride(($product['price'] > 0) ? '1' : '0');
                            $modifyThisProduct->setPrice($product['price']);
                        }
                        $modifyThisProduct->save();
                        unset($modifyThisProduct);
                        $rowsUpdated++;
                    }
                }
                
                $this->messageManager->addSuccess("Products updated: " . $rowsUpdated);
            }
            
        } else {
            $this->messageManager->addError("Invalid CSV");
        }
        ini_set('auto_detect_line_endings',FALSE);
    } else {
        $this->messageManager->addError("Invalid CSV");
    }

        return $this;
    }


}