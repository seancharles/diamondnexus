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
use Magento\Catalog\Model\ResourceModel\Product;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Bundle\Api\Data\LinkInterfaceFactory;
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
     * @var Product
     */
    protected $productResource;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var string[][]
     */
    protected $stoneSkuLookups = [
        'PR' => [
            'USLSSS0009X' => 'USLSSS0010X',
            'USLSSS0010X' => 'USLSSS0020X',
            'USLSSS0020X' => 'USLSSS0025X',
            'USLSSS0025X' => 'USLSSS0031X',
            'USLSSS0031X' => 'USLSSS0034X',
            'USLSSS0034X' => 'USLSSS0038X',
            'USLSSS0038X' => 'USLSSS0056X',
            'USLSSS0056X' => 'USLSSS0064X',
            'USLSSS0064X' => 'USLSSS0079X',
            'USLSSS0079X' => 'USLSSS0084X',
            'USLSCS0010X' => 'USLSCS0020X',
            'USLSCS0020X' => 'USLSCS0025X',
            'USLSCS0025X' => 'USLSCS0031X',
            'USLSCS0031X' => 'USLSCS0034X',
            'USLSCS0034X' => 'USLSCS0038X',
            'USLSCS0038X' => 'USLSCS0056X',
            'USLSCS0056X' => 'USLSCS0064X',
            'USLSCS0064X' => 'USLSCS0079X',
            'USLSCS0079X' => 'USLSCS0084X',
            'USLSCS0084X' => 'USLSCS0009X'
        ],
        'OV' => [
            'USLSSS0008X' => 'USLSSS0017X',
            'USLSSS0017X' => 'USLSSS0030X',
            'USLSSS0030X' => 'USLSSS0039X',
            'USLSSS0039X' => 'USLSSS0052X',
            'USLSSS0052X' => 'USLSSS0063X',
            'USLSSS0063X' => 'USLSSS0078X',
            'USLSCS0008X' => 'USLSCS0017X',
            'USLSCS0017X' => 'USLSCS0030X',
            'USLSCS0030X' => 'USLSCS0039X',
            'USLSCS0039X' => 'USLSCS0052X',
            'USLSCS0052X' => 'USLSCS0063X',
            'USLSCS0063X' => 'USLSCS0078X'
        ],
        'EM' => [
            'USLSSS0007X' => 'USLSSS0005X',
            'USLSSS0005X' => 'USLSSS0019X',
            'USLSSS0019X' => 'USLSSS0024X',
            'USLSSS0024X' => 'USLSSS0029X',
            'USLSSS0029X' => 'USLSSS0035X',
            'USLSSS0035X' => 'USLSSS0041X',
            'USLSSS0041X' => 'USLSSS0051X',
            'USLSSS0051X' => 'USLSSS0061X',
            'USLSSS0061X' => 'USLSSS0075X',
            'USLSCS0007X' => 'USLSCS0005X',
            'USLSCS0005X' => 'USLSCS0019X',
            'USLSCS0019X' => 'USLSCS0024X',
            'USLSCS0024X' => 'USLSCS0029X',
            'USLSCS0029X' => 'USLSCS0035X',
            'USLSCS0035X' => 'USLSCS0041X',
            'USLSCS0041X' => 'USLSCS0051X',
            'USLSCS0051X' => 'USLSCS0061X',
            'USLSCS0061X' => 'USLSCS0075X'
        ],
        'CR' => [
            'USLSSS0003X' => 'USLSSS0006X',
            'USLSSS0006X' => 'USLSSS0014X',
            'USLSSS0014X' => 'USLSSS0022X',
            'USLSSS0022X' => 'USLSSS0054X',
            'USLSSS0054X' => 'USLSSS0074X',
            'USLSCS0003X' => 'USLSCS0006X',
            'USLSCS0006X' => 'USLSCS0014X',
            'USLSCS0014X' => 'USLSCS0022X',
            'USLSCS0022X' => 'USLSCS0054X',
            'USLSCS0054X' => 'USLSCS0074X'
        ],
        'CU' => [
            'USLSSS0003X' => 'USLSSS0006X',
            'USLSSS0006X' => 'USLSSS0014X',
            'USLSSS0014X' => 'USLSSS0022X',
            'USLSSS0022X' => 'USLSSS0054X',
            'USLSSS0054X' => 'USLSSS0074X',
            'USLSCS0003X' => 'USLSCS0006X',
            'USLSCS0006X' => 'USLSCS0014X',
            'USLSCS0014X' => 'USLSCS0022X',
            'USLSCS0022X' => 'USLSCS0054X',
            'USLSCS0054X' => 'USLSCS0074X'
        ],
        'TR' => [
            'USLSSS0012X' => 'USLSSS0046X',
            'USLSSS0046X' => 'USLSSS0059X',
            'USLSSS0059X' => 'USLSSS0066X',
            'USLSSS0066X' => 'USLSSS0083X',
            'USLSCS0012X' => 'USLSCS0046X',
            'USLSCS0046X' => 'USLSCS0059X',
            'USLSCS0059X' => 'USLSCS0066X',
            'USLSCS0066X' => 'USLSCS0083X'
        ],
        'TL' => [
            'USLSSS0011X' => 'USLSSS0009X',
            'USLSSS0009X' => 'USLSSS0048X',
            'USLSSS0048X' => 'USLSSS0058X',
            'USLSSS0058X' => 'USLSSS0069X',
            'USLSSS0069X' => 'USLSSS0082X',
            'USLSCS0011X' => 'USLSCS0009X',
            'USLSCS0009X' => 'USLSCS0048X',
            'USLSCS0048X' => 'USLSCS0058X',
            'USLSCS0058X' => 'USLSCS0069X',
            'USLSCS0069X' => 'USLSCS0082X'
        ],
        'HT' => [
            'USLSSS0010X' => 'USLSSS0011X',
            'USLSSS0011X' => 'USLSSS0043X',
            'USLSSS0043X' => 'USLSSS0076X',
            'USLSCS0010X' => 'USLSCS0011X',
            'USLSCS0011X' => 'USLSCS0043X',
            'USLSCS0043X' => 'USLSCS0076X'
        ],
        'MQ' => [
            'USLSSS0006X' => 'USLSSS0004X',
            'USLSSS0004X' => 'USLSSS0018X',
            'USLSSS0018X' => 'USLSSS0040X',
            'USLSSS0040X' => 'USLSSS0053X',
            'USLSSS0053X' => 'USLSSS0070X',
            'USLSSS0070X' => 'USLSSS0077X',
            'USLSCS0006X' => 'USLSCS0004X',
            'USLSCS0004X' => 'USLSCS0018X',
            'USLSCS0018X' => 'USLSCS0040X',
            'USLSCS0040X' => 'USLSCS0053X',
            'USLSCS0053X' => 'USLSCS0070X',
            'USLSCS0070X' => 'USLSCS0077X'
        ],
        'RA' => [
            'USLSSS0005X' => 'USLSSS0007X',
            'USLSSS0007X' => 'USLSSS0044X',
            'USLSSS0044X' => 'USLSSS0057X',
            'USLSSS0057X' => 'USLSSS0068X',
            'USLSSS0068X' => 'USLSSS0081X',
            'USLSCS0005X' => 'USLSCS0007X',
            'USLSCS0007X' => 'USLSCS0044X',
            'USLSCS0044X' => 'USLSCS0057X',
            'USLSCS0057X' => 'USLSCS0068X',
            'USLSCS0068X' => 'USLSCS0081X'
        ],
        'AS' => [
            'USLSSS0004X' => 'USLSSS0003X',
            'USLSSS0003X' => 'USLSSS0047X',
            'USLSSS0047X' => 'USLSSS0055X',
            'USLSSS0055X' => 'USLSSS0062X',
            'USLSSS0062X' => 'USLSSS0073X',
            'USLSCS0004X' => 'USLSCS0003X',
            'USLSCS0003X' => 'USLSCS0047X',
            'USLSCS0047X' => 'USLSCS0055X',
            'USLSCS0055X' => 'USLSCS0062X',
            'USLSCS0062X' => 'USLSCS0073X'
        ],
        'PC' => [
            'USLSSS0002X' => 'USLSSS0015X',
            'USLSSS0015X' => 'USLSSS0023X',
            'USLSSS0023X' => 'USLSSS0027X',
            'USLSSS0027X' => 'USLSSS0033X',
            'USLSSS0033X' => 'USLSSS0042X',
            'USLSSS0042X' => 'USLSSS0050X',
            'USLSSS0050X' => 'USLSSS0067X',
            'USLSSS0067X' => 'USLSSS0080X',
            'USLSSS0080X' => 'USLSSS0085X',
            'USLSSS0085X' => 'USLSPC0001X',
            'USLSCS0002X' => 'USLSCS0015X',
            'USLSCS0015X' => 'USLSCS0023X',
            'USLSCS0023X' => 'USLSCS0027X',
            'USLSCS0027X' => 'USLSCS0033X',
            'USLSCS0033X' => 'USLSCS0042X',
            'USLSCS0042X' => 'USLSCS0050X',
            'USLSCS0050X' => 'USLSCS0067X',
            'USLSCS0067X' => 'USLSCS0080X',
            'USLSCS0080X' => 'USLSCS0085X',
            'USLSCS0085X' => 'USLSPC0001X'
        ],
        'RB' => [
            'USLSSS0013X' => 'USLSSS0021X',
            'USLSSS0021X' => 'USLSSS0026X',
            'USLSSS0026X' => 'USLSSS0032X',
            'USLSSS0032X' => 'USLSSS0037X',
            'USLSSS0037X' => 'USLSSS0049X',
            'USLSSS0049X' => 'USLSSS0060X',
            'USLSSS0060X' => 'USLSSS0071X',
            'USLSSS0071X' => 'USLSSS0072X',
            'USLSSS0072X' => 'USLSPC0002X',
            'USLSPC0002X' => 'USLSPC0004X',
            'USLSPC0004X' => 'USLSPC0005X',
            'USLSPC0005X' => 'USLSPC0006X',
            'USLSCS0013X' => 'USLSCS0021X',
            'USLSCS0021X' => 'USLSCS0026X',
            'USLSCS0026X' => 'USLSCS0032X',
            'USLSCS0032X' => 'USLSCS0037X',
            'USLSCS0037X' => 'USLSCS0049X',
            'USLSCS0049X' => 'USLSCS0060X',
            'USLSCS0060X' => 'USLSCS0071X',
            'USLSCS0071X' => 'USLSCS0072X',
            'USLSCS0072X' => 'USLSPC0002X'
        ]
    ];

    /**
     * Link constructor.
     * @param Context $context
     * @param LinkInterfaceFactory $linkFactory
     * @param ProductRepository $productRepository
     * @param BundlePriceUse $bundlePriceHelper
     * @param Product $productResource
     * @param Logger $logger
     */
    public function __construct(
        Context $context,
        LinkInterfaceFactory $linkFactory,
        ProductRepository $productRepository,
        BundlePriceUse $bundlePriceHelper,
        Product $productResource,
        Logger $logger
    ) {
        parent::__construct($context);
        $this->linkFactory = $linkFactory;
        $this->productRepository = $productRepository;
        $this->bundlePriceHelper = $bundlePriceHelper;
        $this->productResource = $productResource;
        $this->logger = $logger;
    }

    /**
     * @param string $sku
     * @param float $itemPrice
     * @param string $originalSku
     * @return \Magento\Bundle\Model\Link|void
     */
    public function createNewLink(string $sku, float $itemPrice, string $originalSku)
    {
        try {
            $beforeChange = $sku;
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
                        case 'CU':
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
                        /*if ($stoneForm == 'CU' || $stoneForm == "CR") {
                            $stoneForm = 'CR';
                            $sku = str_replace('CU', 'CR', $sku);
                            $sku = str_replace('USLSSS0003X', 'USLSSS0006X', $sku);
                            $sku = str_replace('USLSCS0003X', 'USLSCS0006X', $sku);
                        }*/
                        $product = $this->productRepository->get($sku . 'XXXX');
                        if ($product->getId() !== null) {
                            $this->bundlePriceHelper->setBundlePrice($product, $itemPrice, $originalSku);
                            return $this->prepareLink($product);
                        } else {
                            $this->logger->info('SKU not found - ' . $sku);
                        }
                    } catch (\Exception $e) {
                        $this->logger->info('SKU not found - ' . $sku);
                        if (substr($sku, -4) == 'XXXX') {
                            $sku = substr($sku, 0, -4);
                        }
                        $product = $this->extensionSearchSku($sku, $stoneForm, $beforeChange);
                        if (!$product) {
                            return;
                        }
                        $this->bundlePriceHelper->setBundlePrice($product, $itemPrice, $originalSku);
                        return $this->prepareLink($product);
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

    /**
     * @param string $sku
     * @param string $stoneForm
     * @return \Magento\Catalog\Model\Product|false
     */
    protected function extensionSearchSku(string $sku, string $stoneForm, string $beforeChange)
    {
        // added the "if (array_key_exists())" because of the following error:
        // In process product ID = 8563
        //    In ErrorHandler.php line 61:
        //    Notice: Undefined index: XX
        if (array_key_exists($stoneForm, $this->stoneSkuLookups)) {
            foreach ($this->stoneSkuLookups[$stoneForm] as $old => $new) {
                $sku = str_replace($old, $new, $sku);
                $id = $this->productResource->getIdBySku($sku);
                if ($id) {
                    try {
                        return $this->productRepository->getById($id);
                    } catch (NoSuchEntityException $e) {
                        return false;
                    }
                }
                $id = $this->productResource->getIdBySku($sku . 'XXXX');
                if ($id) {
                    try {
                        return $this->productRepository->getById($id);
                    } catch (NoSuchEntityException $e) {
                        return false;
                    }
                }
            }
        }
        return false;
    }
}
