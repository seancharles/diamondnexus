<?php

namespace ForeverCompanies\LooseStoneImageScript\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Framework\App\State;
use Magento\Framework\App\Config\ScopeConfigInterface;
use ForeverCompanies\LooseStoneImport\Model\StoneImport;
use Magento\Catalog\Model\Product;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\Io\File;

class StoneImages extends Command
{
    const NAME = 'import_stone_images';
    
    private $state;
    
    protected $scopeConfig;
    protected $storeScope;
    protected $stoneImportModel;
    protected $productModel;
    protected $mediaTmpDir;
    protected $file;
    
    public function __construct(
        State $st,
        ScopeConfigInterface $scopeC,
        StoneImport $stoneI,
        Product $prod,
        DirectoryList $directoryList,
        File $fil
    ) {
            $this->state = $st;
            
            $this->scopeConfig = $scopeC;
            $this->storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
            $this->stoneImportModel = $stoneI;
            $this->productModel = $prod;
            $this->mediaTmpDir = $directoryList->getPath(DirectoryList::MEDIA) . DIRECTORY_SEPARATOR . 'tmp';
            
            $this->file = $fil;
           
            parent::__construct('forevercompanies:loose-stone-image-import');
    }
    
    /**
     * @inheritDoc
     */
    protected function configure()
    {
        $this->setName('forevercompanies:loose-stone-image-import');
        $this->setDescription('Loose Stone Image Import');
        $this->addOption(
            self::NAME,
            null,
            InputOption::VALUE_REQUIRED,
            'Name'
        );
        parent::configure();
    }
    
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->state->setAreaCode(\Magento\Framework\App\Area::AREA_FRONTEND);
        
        if ($name = $input->getOption(self::NAME)) {
            $output->writeln('<info>Provided name is `' . $name . '`</info>');
        }
        
        $this->stoneImportModel->updateCsv();
        $csvArray = $this->stoneImportModel->buildArray();
        
        foreach ($csvArray as $csvArr) {
            $productId = $this->productModel->getIdBySku($csvArr['Certificate #']);
            if ($productId) {
                
                $product = $this->productModel->load($productId);
                $imageFileName = $this->mediaTmpDir . DIRECTORY_SEPARATOR . basename($csvArr['Image Link']);
                $imagePathInfo = pathinfo($imageFileName);
                
                if (!isset($imagePathInfo['extension'])) {
                    if (isset($imagePathInfo['mime']) && $imagePathInfo['mime'] == 'image/jpeg') {
                        $imageFileName .= ".jpg";
                    } else {
                        $imageFileName .= ".jpg";
                    }
                }
                
                $imageResult = $this->file->read($csvArr['Image Link'], $imageFileName);
                
                if ($imageResult) {
                    $product->addImageToMediaGallery(
                        $imageFileName,
                        ['image', 'small_image', 'thumbnail'],
                        false,
                        false
                    );
                }
                
                echo 'done with ' . $product->getName() . '<br />';
            }
        }
        echo 'done!';
    }
}
