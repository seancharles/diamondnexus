<?php
declare(strict_types=1);

namespace ForeverCompanies\CustomAttributes\Console\Command;

use Magento\Framework\App\Area;
use Magento\Framework\App\State;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\StateException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use Magento\Framework\App\ResourceConnection;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Filesystem\DirectoryList;

class TagMatchingImages extends Command
{
    /**
     * @var string
     */
    protected $name = 'forevercompanies:tag-matching-images';
    
    public function __construct(
        State $state,
        ResourceConnection $resourceConnection,
        ProductRepositoryInterface $productRepository,
        DirectoryList $fileSystem
    ) {
        $state->setAreaCode(\Magento\Framework\App\Area::AREA_ADMINHTML);
        $this->resourceConnection = $resourceConnection;
        $this->productRepository = $productRepository;
        $this->fileSystem = $fileSystem;

        $this->connection = $this->resourceConnection->getConnection();

        parent::__construct($this->name);
    }

    /**
     * {@inheritdoc}
     * @throws LocalizedException
     */
    protected function execute(
        InputInterface $input,
        OutputInterface $output
    ) {
        $output->writeln("Get products for media for joining images and tags...");

        $basePath = $this->fileSystem->getRoot();
        
        $crossSellList = $this->connection->fetchAll("SELECT * FROM catalog_product_cross_sell WHERE parent_id = '48962' ORDER BY position ASC;");
        
        foreach($crossSellList as $crossSell)
        {
            // insert the matching band cross sell to db
            $this->connection->query("insert into catalog_product_link (linked_product_id, product_id,link_type_id) values (".$crossSell['product_id'].",".$crossSell['parent_id'].",7);");
        }
        
        // add images to products with label
        foreach($crossSellList as $crossSell)
        {
            $crossSellImageList = $this->connection->fetchAll("SELECT * FROM catalog_product_cross_sell_image WHERE parent_id = '" . $crossSell['parent_id'] . "'  ORDER BY position ASC;");
            
            $product = $this->productRepository->getById($crossSell['parent_id']);
            $product->setStoreId(0);
            
            if(count($crossSellImageList) > 0) {
                foreach($crossSellImageList as $crossSellImage)
                {
                    $path = $basePath . "/pub/" . $crossSellImage['large'];
                    
                    if(file_exists($path) === true) {
                        $product->addImageToMediaGallery($path, array('image'), false, false);
                    } else {
                        echo "file not found: " . $path . "<br />";
                    }
                }
                
                // save product images
                $product->save();
                
                $galleryEntries = $product->getMediaGalleryEntries();
                
                foreach ($galleryEntries as $key => $image) {
                    $params = [];                    
                    
                    $filename = basename($image->getFile(), ".jpg");
                    $fileParts = explode("_", $filename);
                    
                    // parse out the begining of the filename since entries with additional numbers are copies
                    $sql = "SELECT * FROM catalog_product_cross_sell_image WHERE large LIKE '%" . $fileParts[0] . "_" . $fileParts[1] . "%';";
                    $crossSellImageDetail = $this->connection->fetchAll($sql);
                    
                    if(isset($crossSellImageDetail[0]) === true) {
                        // parse out the label as array
                        $labelArray = explode(",", $crossSellImageDetail[0]['label']);
                        
                        foreach($labelArray as $tag) {
                            if($tag) {
                                if(in_array($tag, $this->metalTypes) === true) {
                                    $params[] = "metal--" . $tag;
                                }
                                
                                if(in_array($tag, $this->uiRoles) === true) {
                                    $params[] = "role--" . $tag;
                                }
                            }
                        }
                        
                        if(isset($crossSellImageDetail[0]['title_id']) === true) {
                            switch($crossSellImageDetail[0]['title_id']) {
                                case 1:
                                    $params[] = "matching-type--matching-band";
                                    break;
                                case 2:
                                    $params[] = "matching-type--earring-enhancer";
                                    break;
                                case 3:
                                    $params[] = "matching-type--pendant-enhancer";
                                    break;
                                case 4:
                                    $params[] = "matching-type--ring-enhancer";
                                    break;
                                case 5:
                                    $params[] = "matching-type--matching-chain";
                                    break;
                            }
                        } else {
                            // default to matching band, might not be a good idea?
                            $params[] = "matching-type--matching-band";
                        }
                            
                        $params[] = "matching-id--" . $crossSellImageDetail[0]['product_id'];
                        
                        $label = implode(",", $params);
                        
                        $image->setLabel($label);
                    }
                }
                
                $product->setMediaGalleryEntries($galleryEntries);
                $product->save();
            }
        }

        $output->writeln('Adding tags is complete! Please execute bin/magento indexer:reindex if needed and flush cache.');
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName($this->name);
        $this->setDescription("Tag and assign Matching Bands / Enhancer images");
        parent::configure();
    }
}
