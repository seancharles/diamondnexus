<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\RequisitionList\Test\Block\Product\View;

use Magento\Mtf\Block\Block;
use Magento\Mtf\Client\Locator;

/**
 * Product social links block on the product page
 */
class ProductSocialLinks extends Block
{
    /**
     * Locator value for correspondent link.
     *
     * @var string
     */
    protected $link = '//button/span[contains(text(), "%s")]';

    /**
     * Locator value for "Add to Requisition List" select
     *
     * @var string
     */
    protected $addToRequisitionListSelect = '.requisition-list-button';

    /**
     * Locator for Requisition list name in Requisition list select
     *
     * @var string
     */
    protected $option = '//ul[@class="list-items"]/li/span[contains(text(), "%s")]';

    /**
     * Verify if correspondent link is present or not
     *
     * @param string $linkTitle
     * @return bool
     */
    public function isLinkVisible($linkTitle)
    {
        $link = $this->_rootElement->find(sprintf($this->link, $linkTitle), Locator::SELECTOR_XPATH);
        return $link->isVisible();
    }

    /**
     * Click "Add to Requisition List" link
     *
     * @param string $name
     * @return void
     */
    public function clickAddToRequisitionList($name)
    {
        $this->waitForElementVisible($this->addToRequisitionListSelect);
        $this->_rootElement->find($this->addToRequisitionListSelect)->click();
        $this->_rootElement->find(sprintf($this->option, $name), Locator::SELECTOR_XPATH)->click();
    }
}
