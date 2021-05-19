<?php

namespace ForeverCompanies\Gifts\Observer;

use Exception;
use ForeverCompanies\Gifts\Helper\FreeGift;
use ForeverCompanies\Gifts\Helper\Purchase;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Model\QuoteFactory;

class SalesOrderPlaceAfter implements ObserverInterface
{
    /**
     * @var FreeGift
     */
    protected $giftHelper;
    protected $purchaseHelper;
    
    protected $quoteFactory;

    /**
     * ProcessQuoteObserver constructor.
     * @param FreeGift $helper
     */
    public function __construct(
        FreeGift $giftH,
        Purchase $purchaseH,
        QuoteFactory $quoteF
    ) {
        $this->giftHelper = $giftH;
        $this->purchaseHelper = $purchaseH;
        
        $this->quoteFactory = $quoteF;
    }

    /**
     * Used before quote recollect
     *
     * @param Observer $observer
     * @return void
     * @throws Exception
     */
    public function execute(Observer $observer)
    {
        
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/giftlog.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        $logger->info( 'observer name - ' . $observer->getEvent()->getName() );
        $logger->info( 'order id - ' . $observer->getEvent()->getOrder()->getId() );
        
        $eventName = $observer->getEvent()->getName(); 
        
        
        $freeGift = false;
        if ($this->giftHelper->isEnabledFreeGift()) {
            
            $logger->info('free gift');
            
            $quote = $this->quoteFactory->create()->load($observer->getEvent()->getOrder()->getId());
            
            
            $giftSkus = $this->giftHelper->fillGifts($quote);
            
            foreach ($giftSkus as $sku => $qty) {
                $freeGift = true;
                $this->giftHelper->addGiftToQuote($quote, $sku, $qty, false, true, $observer->getEvent()->getName() );
            }
        }

        if (!$freeGift && $this->purchaseHelper->isEnabledPurchase()) {
            
            $logger->info('purchase');
            
            $quote = $this->quoteFactory->create()->load($observer->getEvent()->getOrder()->getId());
            
            $logger->info( 'quote id - ' . $quote->getId() );
      
            
            $this->purchaseHelper->addGiftToQuote($quote);
        } 
        
        return;
    }
    
}
