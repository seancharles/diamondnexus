<?php
namespace ForeverCompanies\CustomAttributes\Observer;

use Magento\Framework\Event\Observer;

class LockAttributes implements \Magento\Framework\Event\ObserverInterface
{
    public function execute(Observer $observer)
    {
        $product = $observer->getData('product');
        foreach ($product->getOptions() as $option) {
            $product->lockAttribute($option->getData('customization_type'));
        }
    }
}
