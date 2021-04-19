<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace ForeverCompanies\CustomAdmin\Helper;
use Magento\Framework\App\Helper\AbstractHelper;

/**
 * Sales module base helper
 */
class Reorder extends \Magento\Sales\Helper\Reorder
{
    /**
     * @return bool
     */
    public function isAllow()
    {
        return true;
    }

    /**
     * Check if reorder is allowed for given store
     *
     * @param \Magento\Store\Model\Store|int|null $store
     * @return bool
     */
    public function isAllowed($store = null)
    {
        return true;
    }

    /**
     * Check is it possible to reorder
     *
     * @param int $orderId
     * @return bool
     */
    public function canReorder($orderId)
    {
        return true;
    }
}
