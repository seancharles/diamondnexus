<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CompanyCredit\Test\Block\Adminhtml\Order\View\Tab\History;

use Magento\Mtf\Block\Block;

/**
 * Order comments history block.
 */
class CommentsHistoryBlock extends Block
{
    /**
     * Comment history list locator.
     *
     * @var string
     */
    private $commentHistory = '.comments-block-item';

    /**
     * Comment date.
     *
     * @var string
     */
    private $commentHistoryDateTime = '.comments-block-item-date-time';

    /**
     * Comment locator.
     *
     * @var string
     */
    private $comment = '.comments-block-item-comment';

    /**
     * Get comment history block data.
     *
     * @return array
     */
    public function getComments()
    {
        $result = [];
        $elements = $this->_rootElement->getElements($this->commentHistory);
        foreach ($elements as $key => $item) {
            $result[$key] = [
                'date' => $item->find($this->commentHistoryDateTime)->getText(),
                'comment' => '',
            ];
            if ($item->find($this->comment)->isVisible()) {
                $result[$key]['comment'] = $item->find($this->comment)->getText();
            }
        }

        return $result;
    }

    /**
     * Get last comment.
     *
     * @return array
     */
    public function getLatestComment()
    {
        $comments = $this->getComments();
        return end($comments);
    }
}
