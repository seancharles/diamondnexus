<?php

namespace ForeverCompanies\CustomSales\Plugin;

use ForeverCompanies\CustomSales\Block\Adminhtml\Order\Create\CustomFields as Block;
use Magento\Framework\Exception\LocalizedException;
use Magento\Shipping\Block\Adminhtml\Create\Items;

class CustomFields
{
    /**
     * @param Items $subject
     * @param $html
     * @return string
     * @throws LocalizedException
     */
    public function afterToHtml(Items $subject, $html)
    {
        $template = 'ForeverCompanies_CustomSales::order/create/custom_fields.phtml';
        $newBlockHtml = $subject->getLayout()->createBlock(Block::class)->setTemplate($template)->toHtml();
        return $html . $newBlockHtml;
    }
}
