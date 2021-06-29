<?php
namespace ForeverCompanies\CostImport\Controller\Index;

use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Message\ManagerInterface;
use Magento\Catalog\Model\ProductFactory;

class Index extends Action
{
    protected $connection;
    protected $messageManager;
    protected $productFactory;
    
	public function __construct(
		Context $context,
	    ResourceConnection $resourceC,
	    ManagerInterface $managerI,
	    ProductFactory $productF
	) {
		$this->connection = $resourceC->getConnection();
		$this->messageManager = $managerI;
		$this->productFactory = $productF;
	    
		return parent::__construct($context);
	}

	public function execute()
	{
	    echo 'ffff';die;
	    
	    ini_set('auto_detect_line_endings',TRUE);
	    
	    $params = $this->getRequest()->getParams();
	    
	    $headerErrors = [];
	    $products = [];
	    $skus = [];
	    $rowsUpdated = 0;
	    $line = 1;
	    
	    
	    if((1==1) || isset($_FILES['spreadsheet']['name']) && $_FILES['spreadsheet']['name'] != '') {
	  //      $fp = fopen($_FILES['spreadsheet']['tmp_name'],'r');
	       $fp = fopen("/var/www/magento/var/import/price_import.csv", "r");
	        
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
	                
	                
	                /*
	                echo '<pre>';
	                var_dump("result", $result);
	                die;
	                */
	                
	                // Exception #0 (Exception): Warning: Illegal string offset 'id' in 
	                // /var/www/magento/app/code/ForeverCompanies/CostImport/Controller/Index/Index.php on line 117
	                
	                
	                // add the product id to each product
	                foreach($result as $res) {
	                    $products[$res['sku']]['id'] = $res['entity_id'];
	                    $products[$res['sku']]['supplier'] = $res['supplier'];
	                }
	                
	                foreach($products as $sku => $product) {
	                    
	                    /*
	                    echo '<pre>';
	                    var_dump("sku", $sku);
	                    var_dump("product", $product);
	                    
	                    die;
	                    */
	                    
	                    /*
	                     
	                     string(3) "sku"
                            string(10) "LG10219101"
                            string(7) "product"
                            array(4) {
                              ["cost"]=>
                              string(4) "4500"
                              ["price"]=>
                              string(4) "5000"
                              ["id"]=>
                              string(5) "79175"
                              ["supplier"]=>
                              string(1) "4"
                            }
	                     
	                     */
	                    
	                    if(!$product['id'] > 0) {
	                        $this->messageManager->addError("unable to find sku: " . $sku);
	                    } else {
	                        $this->messageManager->addError("this is working ok");
	                        $modifyThisProduct = $this->productFactory->create()->load($product['id']);
	                        
	                        if( $product['supplier'] == "34" || $product['supplier'] == "36" ) {
	                            // update stones intermediary
	                            $this->connection->query("UPDATE stones_intermediary SET final_cost = '" . $product['cost'] . "' WHERE certificate_number = '" . $sku . "';");
	                            
	                            
	                            
	                            $modifyThisProduct->setStoneImportPriceOverride(($product['price'] > 0) ? '1' : '0');
	                            $modifyThisProduct->setPrice($product['price']);
	                            $modifyThisProduct->setStoneImportCostOverride(($product['cost'] > 0) ? '1' : '0');
	                            $modifyThisProduct->setStoneImportCustomCost($product['cost']);
	                            
	                            
	                    //        echo 'the product name is ' . $product->getName();die;
	                            
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
	            $this->_redirect('import/adminhtml_sheet/index');
	        }
	        ini_set('auto_detect_line_endings',FALSE);
	    } else {
	        $this->messageManager->addError("Invalid CSV");
	        $this->_redirect('import/adminhtml_price/index');
	    }
	    $this->loadLayout()->renderLayout();
	}
}