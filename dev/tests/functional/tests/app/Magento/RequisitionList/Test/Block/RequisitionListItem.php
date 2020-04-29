<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\RequisitionList\Test\Block;

use Magento\Mtf\Block\Form;
use Magento\Mtf\Client\Locator;

/**
 * Requisition list item update block.
 */
class RequisitionListItem extends Form
{
    /**
     * Xpath selector for select with options.
     *
     * @var string
     */
    private $select = '//select[parent::div/parent::div/label[contains(., "%s")]]';

    /**
     * Qty input selector.
     *
     * @var string
     */
    private $qtyInput = '.field.qty input';

    /**
     * Update requisition list button selector.
     *
     * @var string
     */
    private $submitButton = '.requisition-list-button.change';

    /**
     * Update requisition list.
     *
     * @param array $updateData
     * @return void
     */
    public function updateRequisitionListItem($updateData)
    {
        if (isset($updateData['options'])) {
            foreach ($updateData['options'] as $key => $optionData) {
                $select = $this->_rootElement->find(sprintf($this->select, $key), Locator::SELECTOR_XPATH, 'select');
                $selectedOptionLabel = $select->getValue();
                $updatedOptionText = str_replace(
                    $optionData['initial_label'],
                    $optionData['label'],
                    $selectedOptionLabel
                );
                $select->setValue($updatedOptionText);
            }
        }
        if (isset($updateData['qty'])) {
            $this->_rootElement->find($this->qtyInput)->setValue($updateData['qty']);
        }
        $this->_rootElement->find($this->submitButton)->click();
    }
}
