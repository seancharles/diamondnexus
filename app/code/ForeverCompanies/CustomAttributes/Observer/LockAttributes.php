<?php

namespace ForeverCompanies\CustomAttributes\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class LockAttributes implements ObserverInterface
{
    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        $product = $observer->getData('product');
        $options = $product->getOptions();
        if ($options != null) {
            foreach ($options as $option) {
                $product->lockAttribute($option->getData('customization_type'));
            }
        }
    }
}
