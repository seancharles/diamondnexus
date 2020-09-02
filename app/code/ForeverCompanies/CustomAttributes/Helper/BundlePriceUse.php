<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace ForeverCompanies\CustomAttributes\Helper;

use ForeverCompanies\CustomAttributes\Logger\Logger;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Bundle\Api\Data\LinkInterfaceFactory;
use Magento\Bundle\Api\Data\LinkInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\InputException;
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

    public function __construct(
        Context $context,
        ProductRepositoryInterface $productRepository,
        Logger $logger
    )
    {
        parent::__construct($context);
        $this->productRepository = $productRepository;
        $this->logger = $logger;
    }

    public function setBundlePrice(Product $product, float $price, string $originalSku)
    {
        $sku = $product->getSku();
        if ($product->getData('bundle_price_use') == 0) {
            $product->setData('bundle_price_use', $price);
            try {
                $this->productRepository->save($product);
            } catch (CouldNotSaveException $e) {
                $this->logger->info("Can\t save bundle_price ($price) for $sku, " . $e->getMessage());
            } catch (InputException $e) {
                $this->logger->info("Can\t save bundle_price ($price) for $sku, " . $e->getMessage());
            } catch (StateException $e) {
                $this->logger->info("Can\t save bundle_price ($price) for $sku, " . $e->getMessage());
            }
        }
        if ($product->getData('bundle_price_use') !== $price) {
            $this->logger->info("Different bundle_price ($price) for $sku when transforming $originalSku");
        }
    }
}
