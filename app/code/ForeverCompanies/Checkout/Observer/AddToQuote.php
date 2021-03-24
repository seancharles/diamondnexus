<?php

namespace ForeverCompanies\Checkout\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
    
class AddToQuote implements ObserverInterface
{
    protected $_checkoutSession;

    public function __construct(
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Checkout\Model\Cart $cart,
        \Magento\Quote\Model\ResourceModel\Quote\Item\CollectionFactory $quoteItemCollectionFactory,
        \Magento\Framework\App\ResourceConnection $resourceConnection
    ) {
        $this->_checkoutSession = $checkoutSession;
        $this->cart = $cart;
        $this->quoteItemCollectionFactory = $quoteItemCollectionFactory;
        $this->resourceConnection = $resourceConnection;
    }
    
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $item = $observer->getEvent()->getData('quote_item');
        
        $setId = $this->_checkoutSession->getBundleIdentifier();

        if($setId > 0) {
            $item->setSetId($setId);
        }

        $quoteId = $this->cart->getQuote()->getId();
        
        $collection = $this->quoteItemCollectionFactory->create();
		$collection->addFieldToFilter('quote_id', $quoteId);
        $collection->addFieldToFilter('set_id', $setId);
        
        if($collection) {
            $parentItem = $collection->getFirstItem();
            
            $parentItemId = $parentItem->getItemId();
            
            $item->setParentItemId($parentItemId);
            
            $rowTotal = $parentItem->getRowTotal() + ($item->getPrice() * $parentItem->getQty());
            
            $connection = $this->resourceConnection->getConnection();
            $connection->query("UPDATE quote_item SET row_total = '" . $rowTotal . "' WHERE item_id = '" . $parentItemId . "';" );
        }
    }
}
