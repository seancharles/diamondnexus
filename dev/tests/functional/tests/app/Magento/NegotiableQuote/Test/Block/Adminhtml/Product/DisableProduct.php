<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\NegotiableQuote\Test\Block\Adminhtml\Product;

use Magento\Mtf\Block\Block;
use Magento\Mtf\Client\Locator;

/**
 * Class CatalogProductEdit
 */
class DisableProduct extends Block
{
    /**
     * Product status checkbox
     *
     * @var string
     */
    private $productStatus = '[name="product[status]"]';

    /**
     * Product status label
     *
     * @var string
     */
    private $productStatusLabel = '//*[@id="container"]/div/div[2]/div[1]/div/fieldset/div[1]/div/div/label';

    /**
     * Set product status
     */
    public function setProductDisabled()
    {
        if ($this->_rootElement->find($this->productStatus)->isSelected()) {
            $this->_rootElement->find($this->productStatusLabel, Locator::SELECTOR_XPATH)->click();
        }
    }
}
