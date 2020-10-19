<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace ForeverCompanies\CustomAttributes\Helper;

use ForeverCompanies\CustomAttributes\Logger\Logger;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\ProductRepository;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Bundle\Api\Data\LinkInterfaceFactory;
use Magento\Bundle\Api\Data\LinkInterface;
use Magento\Framework\Exception\NoSuchEntityException;

class Link extends AbstractHelper
{
    /**
     * @var LinkInterfaceFactory
     */
    protected $linkFactory;

    /**
     * @var ProductRepository
     */
    protected $productRepository;

    /**
     * @var BundlePriceUse
     */
    protected $bundlePriceHelper;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * Link constructor.
     * @param Context $context
     * @param LinkInterfaceFactory $linkFactory
     * @param ProductRepository $productRepository
     * @param BundlePriceUse $bundlePriceHelper
     * @param Logger $logger
     */
    public function __construct(
        Context $context,
        LinkInterfaceFactory $linkFactory,
        ProductRepository $productRepository,
        BundlePriceUse $bundlePriceHelper,
        Logger $logger
    )
    {
        parent::__construct($context);
        $this->linkFactory = $linkFactory;
        $this->productRepository = $productRepository;
        $this->bundlePriceHelper = $bundlePriceHelper;
        $this->logger = $logger;
    }

    /**
     * @param string $sku
     * @param float $itemPrice
     * @param string $originalSku
     * @return \Magento\Bundle\Model\Link
     */
    public function createNewLink(string $sku, float $itemPrice, string $originalSku)
    {
        try {
            $product = $this->productRepository->get($sku);
            if ($product->getId() !== null) {
                $this->bundlePriceHelper->setBundlePrice($product, $itemPrice, $originalSku);
                return $this->prepareLink($product);
            } else {
                $product = $this->productRepository->get($sku . 'XXXX');
                if ($product->getId() !== null) {
                    $this->bundlePriceHelper->setBundlePrice($product, $itemPrice, $originalSku);
                    return $this->prepareLink($product);
                } else {
                    $this->logger->info('SKU not found - ' . $sku);
                }
            }
        } catch (NoSuchEntityException $e) {
            try {
                $product = $this->productRepository->get($sku . 'XXXX');
                if ($product->getId() !== null) {
                    $this->bundlePriceHelper->setBundlePrice($product, $itemPrice, $originalSku);
                    return $this->prepareLink($product);
                } else {
                    $this->logger->info('SKU not found - ' . $sku);
                }
            } catch (\Exception $e) {
                try {
                    $this->logger->info('SKU not found - ' . $sku);
                    $stoneForm = substr($sku, 11, 2);
                    switch ($stoneForm) {
                        case 'PR':
                            $sku = str_replace('USLSSS0001X', 'USLSSS0009X', $sku);
                            $sku = str_replace('USLSCS0001X', 'USLSCS0010X', $sku);
                            break;
                        case 'OV':
                            $sku = str_replace('USLSSS0001X', 'USLSSS0008X', $sku);
                            $sku = str_replace('USLSCS0001X', 'USLSCS0008X', $sku);
                            break;
                        case 'EM':
                            $sku = str_replace('USLSSS0001X', 'USLSSS0007X', $sku);
                            $sku = str_replace('USLSCS0001X', 'USLSCS0007X', $sku);
                            break;
                        case 'CR':
                            $sku = str_replace('USLSSS0001X', 'USLSSS0003X', $sku);
                            $sku = str_replace('USLSCS0001X', 'USLSCS0003X', $sku);
                            break;
                        case 'TR':
                            $sku = str_replace('USLSSS0001X', 'USLSSS0012X', $sku);
                            $sku = str_replace('USLSCS0001X', 'USLSCS0012X', $sku);
                            break;
                        case 'TL':
                            $sku = str_replace('USLSSS0001X', 'USLSSS0011X', $sku);
                            $sku = str_replace('USLSCS0001X', 'USLSCS0011X', $sku);
                            break;
                        case 'HT':
                            $sku = str_replace('USLSSS0001X', 'USLSSS0010X', $sku);
                            $sku = str_replace('USLSCS0001X', 'USLSCS0010X', $sku);
                            break;
                        case 'MQ':
                            $sku = str_replace('USLSSS0001X', 'USLSSS0006X', $sku);
                            $sku = str_replace('USLSCS0001X', 'USLSCS0006X', $sku);
                            break;
                        case 'RA':
                            $sku = str_replace('USLSSS0001X', 'USLSSS0005X', $sku);
                            $sku = str_replace('USLSCS0001X', 'USLSCS0005X', $sku);
                            break;
                        case 'AS':
                            $sku = str_replace('USLSSS0001X', 'USLSSS0004X', $sku);
                            $sku = str_replace('USLSCS0001X', 'USLSCS0004X', $sku);
                            break;
                        case 'PC':
                            $sku = str_replace('USLSSS0001X', 'USLSSS0002X', $sku);
                            $sku = str_replace('USLSCS0001X', 'USLSCS0002X', $sku);
                            break;
                    }
                    $product = $this->productRepository->get($sku);
                    if ($product->getId() !== null) {
                        $this->bundlePriceHelper->setBundlePrice($product, $itemPrice, $originalSku);
                        return $this->prepareLink($product);
                    } else {
                        $this->logger->info('SKU not found - ' . $sku);
                    }
                } catch (\Exception $e) {
                    try {
                        $product = $this->productRepository->get($sku . 'XXXX');
                        if ($product->getId() !== null) {
                            $this->bundlePriceHelper->setBundlePrice($product, $itemPrice, $originalSku);
                            return $this->prepareLink($product);
                        } else {
                            $this->logger->info('SKU not found - ' . $sku);
                        }
                    } catch (\Exception $e) {
                        $this->logger->info('SKU not found - ' . $sku);
                    }
                }
            }
        }
    }

    /**
     * @param ProductInterface $product
     * @return \Magento\Bundle\Model\Link
     */
    public function prepareLink(ProductInterface $product)
    {
        /** @var \Magento\Bundle\Model\Link $link */
        $link = $this->linkFactory->create();
        $link->setSku($product->getSku());
        $link->setData('name', $product->getName());
        $link->setData('selection_qty', 1);
        $link->setData('qty', 1);
        $link->setData('can_change_qty', 1);
        $link->setData('product_id', $product->getId());
        $link->setData('record_id', $product->getId());
        $link->setIsDefault(false);
        return $link;
    }
}
