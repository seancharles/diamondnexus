<?php

namespace ForeverCompanies\CustomSales\Plugin;

use ForeverCompanies\CustomSales\Block\Adminhtml\Order\Create\CustomFields as Block;
use Magento\Sales\Block\Adminhtml\Order\Create\Comment;

class CustomFields
{
    /**
     * @param \Magento\Shipping\Block\Adminhtml\Create\Items $subject
     * @param $html
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function afterToHtml(\Magento\Shipping\Block\Adminhtml\Create\Items $subject, $html)
    {
        $template = 'ForeverCompanies_CustomSales::order/create/custom_fields.phtml';
        $newBlockHtml = $subject->getLayout()->createBlock(Block::class)->setTemplate($template)->toHtml();
        return $html . $newBlockHtml;
    }
}
