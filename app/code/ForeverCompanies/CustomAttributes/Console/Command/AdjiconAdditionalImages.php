<?php

namespace ForeverCompanies\CustomAttributes\Console\Command;

use Magento\Framework\App\State;
use Magento\Framework\Exception\LocalizedException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Filesystem\DirectoryList;

class AdjiconAdditionalImages extends Command
{
    protected array $metalTypes = [
        '18K White Gold',
        '18K Yellow Gold',
        '14K White Gold',
        '14K Yellow Gold',
        '14K Rose Gold'
    ];

    protected array $uiRoles = [
        'Default',
        'Hover',
        'Base',
        'Small',
        'Swatch',
        'Thumbnail',
        'Matching-Hover',
        'Matching-Default'
    ];

    /**
     * @var string
     */
    protected $name = 'forevercompanies:adjicon-additional-images';

    protected $state;
    protected $resourceConnection;
    protected $productRepository;
    protected $fileSystem;
    protected $connection;

    /**
     * TagMatchingImages constructor.
     * @param State $state
     * @param ResourceConnection $resourceConnection
     * @param ProductRepositoryInterface $productRepository
     * @param DirectoryList $fileSystem
     */
    public function __construct(
        State $state,
        ResourceConnection $resourceConnection,
        ProductRepositoryInterface $productRepository,
        DirectoryList $fileSystem
    ) {
        $this->state = $state;
        $this->resourceConnection = $resourceConnection;
        $this->productRepository = $productRepository;
        $this->fileSystem = $fileSystem;

        $this->connection = $this->resourceConnection->getConnection();

        parent::__construct($this->name);
    }

    protected function execute(
        InputInterface $input,
        OutputInterface $output
    ) {
        try {
            $this->state->setAreaCode(\Magento\Framework\App\Area::AREA_ADMINHTML);

            $output->writeln("Running additional images command...");

            $basePath = $this->fileSystem->getRoot();
            $productList = [];

            // join on catalog product entity to prevent from loading products that do not exist.
            $adjiconImageList = $this->connection->fetchAll(
                "SELECT
                    i.product_id, i.option_id, i.file
                FROM
                    m1_adjicon_image i
                INNER JOIN
                    catalog_product_entity e ON i.product_id = e.entity_id
                AND
                    e.entity_id NOT IN(56053, 56057, 56065);"
            );

            foreach ($adjiconImageList as $imageDetail) {
                $productId = $imageDetail['product_id'];

                $productList[$productId][] = [
                    'file' => $imageDetail['file'],
                    'option_id' => $imageDetail['option_id']
                ];
            }

            foreach($productList as $productId => $imagesList) {
                $output->writeln("Processing product_id:  ". $productId);

                $product = $this->productRepository->getById($productId);
                $product->setStoreId(0);

                $galleryEntries = $product->getMediaGalleryEntries();

                foreach($imagesList as $imageDetail) {
                    $path = $basePath . "/pub/media/upload/" . $imageDetail['file'];

                    $imageExists = false;

                    // get the current images name
                    $currentFilename = basename($imageDetail['file'], ".jpg");
                    $currentFileMatchName = explode("_", $currentFilename);

                    if (isset($currentFileMatchName[0]) === true && isset($currentFileMatchName[1]) === true) {
                        foreach ($galleryEntries as $key => $image) {
                            $matchFilename = basename($image->getFile(), ".jpg");
                            $matchFileParts = explode("_", $matchFilename);

                            if (isset($matchFileParts[0]) === true && isset($matchFileParts[1]) === true) {
                                if ($currentFileMatchName[0] == $matchFileParts[0] && $currentFileMatchName[1] == $matchFileParts[1]) {
                                    $imageExists = true;
                                }
                            }

                            if (file_exists($path) === true) {
                                if ($imageExists !== true) {
                                    $product->addImageToMediaGallery($path, array('image'), false, false);
                                } else {
                                    $output->writeln("File already exists: " . $path);
                                }
                            } else {
                                $output->writeln("File not found: " . $path);
                            }
                        }
                    }
                }

                // get gallery list again with added images and set labels
                $galleryEntries = $product->getMediaGalleryEntries();

                foreach ($galleryEntries as $key => $image) {
                    $params = [];

                    $filename = basename($image->getFile(), ".jpg");
                    $fileParts = explode("_", $filename);

                    //$image->setLabel('test');
                }

                if (count($galleryEntries) > 0) {
                    $product->setMediaGalleryEntries($galleryEntries);
                }

                $product->save();
                exit;
            }

        } catch (\Exception $e) {
            echo $e->getMessage() . "\n";
        }

        $output->writeln('Adding images is complete!');
    }

    protected function configure()
    {
        $this->setName($this->name);
        $this->setDescription("Add additional images to TF products.");
        parent::configure();
    }
}
