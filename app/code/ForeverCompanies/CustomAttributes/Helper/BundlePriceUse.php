<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace ForeverCompanies\CustomAttributes\Helper;

use ForeverCompanies\CustomAttributes\Logger\Logger;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\CatalogStaging\Model\ResourceModel\ProductSequence;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\StateException;

class BundlePriceUse extends AbstractHelper
{

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var ResourceConnection
     */
    protected $resource;

    /**
     * @var ProductType
     */
    private $productTypeHelper;

    /**
     * BundlePriceUse constructor.
     * @param Context $context
     * @param ProductRepositoryInterface $productRepository
     * @param ProductType $productTypeHelper
     * @param ResourceConnection $resource
     * @param Logger $logger
     */
    public function __construct(
        Context $context,
        ProductRepositoryInterface $productRepository,
        ProductType $productTypeHelper,
        ResourceConnection $resource,
        Logger $logger
    ) {
        parent::__construct($context);
        $this->productRepository = $productRepository;
        $this->productTypeHelper = $productTypeHelper;
        $this->resource = $resource;
        $this->logger = $logger;
    }

    public function setBundlePrice(Product $product, float $price, string $originalSku)
    {
        $tableName = $this->resource->getTableName(ProductSequence::SEQUENCE_TABLE);
        $this->resource->getConnection()->insertOnDuplicate($tableName, ['sequence_value' => $product->getId()]);
        $sku = $product->getSku();
        if ($product->getData('bundle_price_use') == 0 || $product->getData('bundle_price_use') !== $price) {
            $product->setData('bundle_price_use', $price);
            try {
                $this->productTypeHelper->setProductType($product);
                $this->productRepository->save($product);
            } catch (CouldNotSaveException $e) {
                $this->logger->info("Can\t save bundle_price ($price) for $sku, " . $e->getMessage());
            } catch (InputException $e) {
                $this->logger->info("Can\t save bundle_price ($price) for $sku, " . $e->getMessage());
            } catch (StateException $e) {
                $this->logger->info("Can\t save bundle_price ($price) for $sku, " . $e->getMessage());
            } catch (NoSuchEntityException $e) {
                $this->logger->error("Can't set product_type for $sku - {$e->getMessage()}");
            }
        }
        if ($product->getData('bundle_price_use') !== $price) {
            $this->logger->info("Different bundle_price ($price) for $sku when transforming $originalSku");
        }
    }
}
