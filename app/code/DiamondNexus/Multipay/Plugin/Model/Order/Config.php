<?php

namespace DiamondNexus\Multipay\Plugin\Model\Order;

class Config
{
    /**
     * @param \Magento\Sales\Model\Order\Config $subject
     * @param $state
     * @return string
     */
    public function beforeGetStateStatuses(
        \Magento\Sales\Model\Order\Config $subject,
        $state
    ) {
        if ($state == 'quote') {
            $state = 'new';
        }
        if ($state == 'pending') {
            $state = 'pending_payment';
        }
        return $state;
    }
}
