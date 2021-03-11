<?php

namespace ForeverCompanies\CustomApi\Plugin\Adminhtml;

use Magento\Checkout\Api\Data\PaymentDetailsInterface;
use Magento\Framework\View\LayoutInterface;
use Magento\Sales\Block\Adminhtml\Order\View\Items\Renderer\DefaultRenderer;

class DefaultRendererPlugin
{
    /**
     * Set additional columns for items
     *
     * @param DefaultRenderer $subject
     * @param LayoutInterface $result
     *
     * @return array|PaymentDetailsInterface|LayoutInterface
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetColumns(
        DefaultRenderer $subject,
        $result
    ) {
        if (!empty($result)) {
            $extra = ['flag_loose_stone' => 'col-flag_loose_stone'];
            $result = array_merge($result, $extra);
        }
        return $result;
    }
}
