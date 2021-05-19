<?php

namespace ForeverCompanies\Gifts\Observer;

use Exception;
use ForeverCompanies\Gifts\Helper\FreeGift;
use ForeverCompanies\Gifts\Helper\Purchase;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Model\Quote;
use Magento\Checkout\Model\Cart;
use Magento\Backend\Model\Session\Quote as AdminQuote;

class SalesOrderPlaceAfter implements ObserverInterface
{
    /**
     * @var FreeGift
     */
    protected $giftHelper;
    protected $purchaseHelper;
    
    protected $cart;
    protected $adminQuote;

    /**
     * ProcessQuoteObserver constructor.
     * @param FreeGift $helper
     */
    public function __construct(
        FreeGift $giftH,
        Purchase $purchaseH,
        Cart $crt, 
        AdminQuote $adminQ
    ) {
        $this->giftHelper = $giftH;
        $this->purchaseHelper = $purchaseH;
        
        $this->cart = $crt;
        $this->adminQuote = $adminQ;
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
        echo 'fff';die;
        
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/giftlog.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        $logger->info( 'observer name - ' . $observer->getEvent()->getName() );
        
        $eventName = $observer->getEvent()->getName(); 
        
        
        $freeGift = false;
        if ($this->giftHelper->isEnabledFreeGift()) {
            
            $logger->info('free gift');
            
            $quote = $this->adminQuote->getQuote();
            
            
            $giftSkus = $this->giftHelper->fillGifts($quote);
            
            foreach ($giftSkus as $sku => $qty) {
                $freeGift = true;
                $this->giftHelper->addGiftToQuote($quote, $sku, $qty, false, true, $observer->getEvent()->getName() );
            }
        }

        if (!$freeGift && $this->purchaseHelper->isEnabledPurchase()) {
            
            $logger->info('purchase');
            
            $quote = $this->adminQuote->getQuote();
            
            $logger->info( 'quote id - ' . $quote->getId() );
            $logger->info('admin quote id - ' . $this->adminQuote->getQuote()->getId() );
            
            
            
            $this->purchaseHelper->addGiftToQuote($quote);
        } 
        
        return;
    }
    
}
