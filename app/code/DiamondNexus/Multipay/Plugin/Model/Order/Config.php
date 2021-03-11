<?php

namespace DiamondNexus\Multipay\Plugin\Model\Order;

class Config
{
    /**
     * @param \Magento\Sales\Model\Order\Config $subject
     * @param $state
     */
    public function beforeGetStateStatuses(
        \Magento\Sales\Model\Order\Config $subject,
        $state
    ) {
        if ($state == 'quote') {
            $state = 'new';
        }
        return $state;
    }
}
