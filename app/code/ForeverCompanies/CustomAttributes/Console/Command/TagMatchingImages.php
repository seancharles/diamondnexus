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
use Magento\Catalog\Api\Data\productLinkInterfaceFactory;

class TagMatchingImages extends Command
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
    protected string $name = 'forevercompanies:tag-matching-images';

    protected \Magento\Framework\App\State $state;
    protected ResourceConnection $resourceConnection;
    protected ProductRepositoryInterface $productRepository;
    protected DirectoryList $fileSystem;
    protected productLinkInterfaceFactory $productLinkInterfaceFactory;
    protected \Magento\Framework\DB\Adapter\AdapterInterface $connection;

    /**
     * TagMatchingImages constructor.
     * @param State $state
     * @param ResourceConnection $resourceConnection
     * @param ProductRepositoryInterface $productRepository
     * @param DirectoryList $fileSystem
     * @param productLinkInterfaceFactory $productLinkInterfaceFactory
     */
    public function __construct(
        State $state,
        ResourceConnection $resourceConnection,
        ProductRepositoryInterface $productRepository,
        DirectoryList $fileSystem,
        productLinkInterfaceFactory $productLinkInterfaceFactory
    ) {
        $this->state = $state;
        $this->resourceConnection = $resourceConnection;
        $this->productRepository = $productRepository;
        $this->fileSystem = $fileSystem;
        $this->productLinkInterfaceFactory = $productLinkInterfaceFactory;

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
        try {
            $this->state->setAreaCode(\Magento\Framework\App\Area::AREA_ADMINHTML);

            $output->writeln("Clearing all matching band related entries");

            // clear entries before running
            $this->connection->query("DELETE FROM catalog_product_link WHERE link_type_id IN(7);");

            $output->writeln("Get products for media for joining images and tags...");

            $basePath = $this->fileSystem->getRoot();

            $ignoredProducts = [
                85211,
                67233
            ];

            $crossSellParentList = $this->connection->fetchAll(
                "SELECT c.parent_id FROM catalog_product_cross_sell c INNER JOIN catalog_product_entity e ON c.parent_id = e.entity_id GROUP BY parent_id ORDER BY c.id ASC;"
            );

            foreach ($crossSellParentList as $crossSellParent) {
                $parentId = $crossSellParent['parent_id'];

                $output->writeln($parentId);

                $links = [];

                $parentProduct = $this->productRepository->getById($parentId);
                $parentProduct->setStoreId(0);

                if ($parentProduct->getStatus() == 1 && in_array($parentProduct->getId(), $ignoredProducts) !== true) {
                    $crossSellChildList = $this->connection->fetchAll(
                        "SELECT
                            product_id
                         FROM
                            catalog_product_cross_sell c
                         INNER JOIN
                            catalog_product_entity e ON c.product_id = e.entity_id
                         WHERE
                            parent_id = '" . $parentId . "';"
                    );

                    foreach ($crossSellChildList as $crossSellChild) {
                        $productId = $crossSellChild['product_id'];

                        $output->writeln(" - " . $productId);

                        $childProduct = $this->productRepository->getById($productId);

                        if ($childProduct->getStatus() == 1) {
                            $productLink = $this->productLinkInterfaceFactory->create();

                            $productLink->setSku($parentProduct->getSku())
                                    ->setLinkType('accessory')
                                    ->setLinkedProductSku($childProduct->getSku())
                                    ->setLinkedProductType($childProduct->getTypeId());
                            $links[] = $productLink;
                        }
                    }

                    // handle adding images
                    $crossSellImageList = $this->connection->fetchAll("SELECT * FROM catalog_product_cross_sell_image WHERE parent_id = '" . $parentId . "'  ORDER BY position ASC;");

                    $galleryEntries = $parentProduct->getMediaGalleryEntries();

                    foreach ($crossSellImageList as $crossSellImage) {
                        if ($crossSellImage['large'] != null) {
                            $path = $basePath . "/pub/" . $crossSellImage['large'];

                            $imageExists = false;

                            // get the current images name
                            $currentFilename = basename($crossSellImage['large'], ".jpg");
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
                                }
                            }

                            if (file_exists($path) === true) {
                                if ($imageExists !== true) {
                                    $parentProduct->addImageToMediaGallery($path, array('image'), false, false);
                                } else {
                                    $output->writeln("File already exists: " . $path);
                                }
                            } else {
                                $output->writeln("File not found: " . $path);
                            }
                        }
                    }

                    // retrieve the gallery entries again to pick up the ones added above
                    $galleryEntries = $parentProduct->getMediaGalleryEntries();

                    foreach ($galleryEntries as $key => $image) {
                        $params = [];

                        $filename = basename($image->getFile(), ".jpg");
                        $fileParts = explode("_", $filename);

                        if (isset($fileParts[0]) === true && isset($fileParts[1]) === true) {
                            // parse out the begining of the filename since entries with additional numbers are copies
                            $sql = "SELECT * FROM catalog_product_cross_sell_image WHERE large LIKE '%" . $fileParts[0] . "_" . $fileParts[1] . "%';";
                            $crossSellImageDetail = $this->connection->fetchAll($sql);

                            if (isset($crossSellImageDetail[0]) === true) {
                                // parse out the label as array
                                $labelArray = explode(",", $crossSellImageDetail[0]['label']);

                                foreach ($labelArray as $tag) {
                                    if ($tag) {
                                        if (in_array($tag, $this->metalTypes) === true) {
                                            $params[] = "metal--" . $tag;
                                        }

                                        if (in_array($tag, $this->uiRoles) === true) {
                                            $params[] = "role--" . $tag;
                                        }
                                    }
                                }

                                if (isset($crossSellImageDetail[0]['title_id']) === true) {
                                    switch ($crossSellImageDetail[0]['title_id']) {
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
                    }

                    if (count($galleryEntries) > 0) {
                        $parentProduct->setMediaGalleryEntries($galleryEntries);
                    }

                    if (count($links) > 0) {
                        $parentProduct->setProductLinks($links);
                    }

                    $parentProduct->save();
                }
            }
        } catch (\Exception $e) {
            echo $e->getMessage() . "\n";
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
