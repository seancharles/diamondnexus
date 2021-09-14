<?php

namespace ForeverCompanies\Checkout\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
    
class QuoteRemoveItem implements ObserverInterface
{
    protected $cart;
    protected $logger;

    public function __construct(
        \Magento\Checkout\Model\Cart $cart,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->cart = $cart;
    }
    
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $item = $observer->getEvent()->getData('quote_item');
        $setId = $item->getSetId();

        if($setId > 0) {
            $quoteItemList = $this->cart->getQuote()->getAllItems();;
            foreach ($quoteItemList as $quoteItem) {
                if ($quoteItem->getSetId() == $setId) {
                    $quoteItem->delete();
                }
            }
            // removing cart items means we need to notify magento to update the totals
            $this->cart->getQuote()->setTotalsCollectedFlag(false);
        }
    }
}
