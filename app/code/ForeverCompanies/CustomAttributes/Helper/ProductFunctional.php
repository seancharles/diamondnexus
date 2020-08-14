<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace ForeverCompanies\CustomAttributes\Helper;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product;
use Magento\Framework\App\Helper\AbstractHelper;

class ProductFunctional extends AbstractHelper
{
    /**
     * @var Product[]
     */
    protected $productsForDelete = [];

    /**
     * @param string $sku
     * @return string
     */
    public function getStoneSkuFromProductSku(string $sku)
    {
        $lastSymbols = substr($sku, 11, 13);
        $lastSymbol = substr($lastSymbols, -1);
        if (substr($lastSymbols, 10, 2) == 'CS') {
            $lastSymbols = substr($lastSymbols, 0, 10) . '00' . $lastSymbol;
        }
        $firstSymbols = '';
        if ($lastSymbol == '0') {
            $firstSymbols = 'USLSCS0001X';
        }
        if ($lastSymbol == '1') {
            $firstSymbols = 'USLSSS0001X';
        }

        return $firstSymbols . $lastSymbols . 'XXXX';
    }

    /**
     * @return Product[]
     */
    public function getProductForDelete()
    {
        return $this->productsForDelete;
    }

    /**
     * @param Product $product
     */
    public function addProductToDelete($product)
    {
        $this->productsForDelete[] = $product;
    }

    public function clearProductForDelete()
    {
        $this->productsForDelete = [];
    }
}
