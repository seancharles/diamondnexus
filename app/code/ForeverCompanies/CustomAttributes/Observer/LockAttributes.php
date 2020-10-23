<?php

namespace ForeverCompanies\CustomAttributes\Observer;

use Magento\Framework\Event\Observer;

class LockAttributes implements \Magento\Framework\Event\ObserverInterface
{
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
