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

use Exception;
use Mageplaza\EditOrder\Block\Adminhtml\Logs\View;

/**
 * Class Account
 * @package Mageplaza\EditOrder\Block\Adminhtml\Logs\Order
 */
class Account extends View
{
    /**
     * @param array $data
     *
     * @return mixed
     */
    public function getOldEmail($data)
    {
        if ($data['modify-type'] === 'edit') {
            return $data['email'];
        }

        return $data['email-select'];
    }

    /**
     * @param int $groupId
     *
     * @return string
     */
    public function getCustomerGroupName($groupId)
    {
        try {
            $group = $this->groupRepository->getById($groupId);
            $name = $group->getCode();
        } catch (Exception $e) {
            $name = $groupId;
        }

        return $name;
    }
}
