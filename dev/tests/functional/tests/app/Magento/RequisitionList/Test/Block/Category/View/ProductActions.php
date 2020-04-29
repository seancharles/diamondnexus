<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\RequisitionList\Test\Block\Category\View;

use Magento\Mtf\Block\Block;
use Magento\Mtf\Client\Locator;

/**
 * Product actions links block on the category page
 */
class ProductActions extends Block
{
    /**
     * Locator value for correspondent link
     *
     * @var string
     */
    protected $link = '//button/span[contains(text(), "%s")]';

    /**
     * Locator value for product item block
     *
     * @var string
     */
    protected $productItem = '.product-item';

    /**
     * Locator value for product item actions block
     *
     * @var string
     */
    protected $actions = '.product-item-actions';

    /**
     * Verify if correspondent link is present or not
     *
     * @param string $linkTitle
     * @return bool
     */
    public function isLinkVisible($linkTitle)
    {
        $this->_rootElement->find($this->productItem)->hover();
        $this->waitForElementVisible($this->actions);
        $link = $this->_rootElement->find(sprintf($this->link, $linkTitle), Locator::SELECTOR_XPATH);
        return $link->isVisible();
    }
}
