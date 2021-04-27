<?php

namespace ForeverCompanies\Checkout\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
    
class QuoteRemoveItem implements ObserverInterface
{
    protected $_checkoutSession;

    public function __construct(
        \Magento\Checkout\Model\Cart $cart,
        \Magento\Quote\Model\ResourceModel\Quote\Item\CollectionFactory $quoteItemCollectionFactory
    ) {
        $this->cart = $cart;
        $this->quoteItemCollectionFactory = $quoteItemCollectionFactory;
    }
    
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $item = $observer->getEvent()->getData('quote_item');

        if($item->getSetId() > 0) {
            $collection = $this->quoteItemCollectionFactory->create();
            $collection->addFieldToFilter('quote_id', $this->cart->getQuote()->getId());
            $collection->addFieldToFilter('set_id', $item->getSetId());
            
            if($collection) {
                foreach($collection as $qoteItem) {
                    $qoteItem->delete();
                }
            }
        }
    }
}
