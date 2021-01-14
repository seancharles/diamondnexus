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

namespace Mageplaza\EditOrder\Block\Adminhtml\Logs\Order;

use Mageplaza\EditOrder\Block\Adminhtml\Logs\View;

/**
 * Class Items
 * @package Mageplaza\EditOrder\Block\Adminhtml\Logs\Order
 */
class Items extends View
{
    /**
     * @return array
     */
    public function getOldItemsData()
    {
        $oldData = $this->getOldOrderData();
        if (isset($oldData['item'])) {
            return $oldData['item'];
        }

        return [];
    }

    /**
     * @return array
     */
    public function getNewItemsData()
    {
        $newData = $this->getNewOrderData();
        if (isset($newData['item'])) {
            return $newData['item'];
        }

        return [];
    }

    /**
     * @return bool
     */
    public function checkItemsUpdated()
    {
        $oldData = $this->getOldOrderData();
        $newData = $this->getNewOrderData();

        if (isset($newData['item'])) {
            return $newData['item'] !== $oldData['item'];
        }

        return false;
    }

    /**
     * Check if has old items
     *
     * @param int $proId
     *
     * @return bool
     */
    public function hasOldItem($proId)
    {
        return isset($this->getOldItemsData()[$proId]);
    }

    /**
     * @param int $proId
     * @param string $key
     *
     * @return bool
     */
    public function isUpdated($proId, $key)
    {
        if ($newItemsData = $this->getNewItemsData()) {
            if (!isset($newItemsData[$proId])) {
                return false;
            }

            if (!isset($this->getOldItemsData()[$proId])) {
                return true;
            }

            return $newItemsData[$proId][$key] !== $this->getOldItemsData()[$proId][$key];
        }

        return false;
    }

    /**
     * @param int $proId
     *
     * @return string
     */
    public function getAction($proId)
    {
        $oldItemsData = $this->getOldItemsData();
        $newItemsData = $this->getNewItemsData();

        if (isset($oldItemsData[$proId]) && !isset($newItemsData[$proId])) {
            return __('Remove');
        }

        if (!isset($oldItemsData[$proId]) && isset($newItemsData[$proId])) {
            return __('Add');
        }

        return '';
    }

    /**
     * @return array
     */
    public function getItems()
    {
        return array_replace_recursive($this->getNewItemsData(), $this->getOldItemsData());
    }
}
