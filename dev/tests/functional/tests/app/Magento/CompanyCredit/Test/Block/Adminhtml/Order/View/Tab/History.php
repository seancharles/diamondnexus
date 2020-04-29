<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CompanyCredit\Test\Block\Adminhtml\Order\View\Tab;

use Magento\Backend\Test\Block\Widget\Tab;
use Magento\CompanyCredit\Test\Block\Adminhtml\Order\View\Tab\History\CommentsHistoryBlock;

/**
 * History tab.
 */
class History extends Tab
{
    /**
     * Order comments history block.
     *
     * @var string
     */
    protected $commentsHistoryBlock = '.edit-order-comments-block';

    /**
     * Returns Comments history block.
     *
     * @return CommentsHistoryBlock
     */
    public function getCommentsHistoryBlock()
    {
        return $this->blockFactory->create(
            CommentsHistoryBlock::class,
            ['element' => $this->_rootElement->find($this->commentsHistoryBlock)]
        );
    }
}
