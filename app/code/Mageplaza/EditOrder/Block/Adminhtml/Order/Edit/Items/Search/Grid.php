<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_EditOrder
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\EditOrder\Block\Adminhtml\Order\Edit\Items\Search;

use Magento\Sales\Block\Adminhtml\Order\Create\Search\Grid as SearchGrid;

/**
 * Class Grid
 * @package Mageplaza\EditOrder\Block\Adminhtml\Order\Edit\Items\Search
 */
class Grid extends SearchGrid
{
    /**
     * Get search grid item url
     *
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl(
            'sales/order_edit/loadBlock',
            ['block' => 'search_grid', '_current' => true, 'collapse' => null]
        );
    }
}
