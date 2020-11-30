<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ScheduledImportExport\Model;

use LogicException;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ProductRepository;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Filesystem;
use Magento\ScheduledImportExport\Model\Scheduled\Operation;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Indexer\TestCase;

/**
 * @magentoDbIsolation disabled
 */
class ImportTest extends TestCase
{
    public static function setUpBeforeClass()
    {
        $db = Bootstrap::getInstance()->getBootstrap()
            ->getApplication()
            ->getDbInstance();
        if (!$db->isDbDumpExists()) {
            throw new LogicException('DB dump does not exist.');
        }
        $db->restoreFromDbDump();

        parent::setUpBeforeClass();
    }

    public function testRunSchedule()
    {
        $this->assertNull($this->getProduct('product_100500'));
        $this->doImport(
            [
                'file_name' => 'product.csv',
                'server_type' => 'file',
                'file_path' => 'dev/tests/integration/testsuite/Magento/ScheduledImportExport/_files',
            ]
        );
        $this->assertNotNull($this->getProduct('product_100500'));
    }

    public function testRunScheduleWithUTF8EncodedFile()
    {
        $this->assertNull($this->getProduct('product_100501'));
        $filePath = 'dev/tests/integration/testsuite/Magento/ScheduledImportExport/_files/product.csv';
        /** @var Filesystem $fileSystem */
        $fileSystem = Bootstrap::getObjectManager()->get(Filesystem::class);
        $tmpDir = $fileSystem->getDirectoryWrite(DirectoryList::TMP);
        $rootDir = $fileSystem->getDirectoryWrite(DirectoryList::ROOT);
        $tmpFilename = uniqid('test_import_') . '.csv';
        $byteOrderMak = pack('CCC', 0xef, 0xbb, 0xbf);
        $content = $rootDir->readFile($filePath);
        //change sku suffix to make sure a new product is created
        $content = str_replace('100500', '100501', $content);
        $content = $byteOrderMak . utf8_encode($content);
        $tmpDir->writeFile($tmpFilename, $content);
        $this->doImport(
            [
                'file_name' => $tmpFilename,
                'server_type' => 'file',
                'file_path' => $rootDir->getRelativePath($tmpDir->getAbsolutePath()),
            ]
        );
        $this->assertNotNull($this->getProduct('product_100501'));
    }

    /**
     * @param array $fileInfo
     */
    private function doImport(array $fileInfo): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $model = Bootstrap::getObjectManager()->create(
            Import::class,
            [
                'data' => [
                    'entity' => 'catalog_product',
                    'behavior' => 'append',
                ],
            ]
        );
        $operation = $objectManager->create(Operation::class);
        $operation->setFileInfo($fileInfo);
        $model->runSchedule($operation);
    }

    /**
     * @param string $sku
     * @return Product|null
     */
    private function getProduct(string $sku): ?Product
    {
        /** @var ProductRepository $productRepository */
        $productRepository = Bootstrap::getObjectManager()->get(ProductRepository::class);
        try {
            $product = $productRepository->get($sku, false, null, true);
        } catch (NoSuchEntityException $exception) {
            $product = null;
        }
        return $product;
    }
}
