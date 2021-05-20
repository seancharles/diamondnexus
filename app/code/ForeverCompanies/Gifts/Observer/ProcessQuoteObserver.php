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
use Magento\Checkout\Model\Session as CheckoutSession;

class ProcessQuoteObserver implements ObserverInterface
{
    /**
     * @var FreeGift
     */
    protected $giftHelper;
    protected $purchaseHelper;
    
    protected $cart;
    protected $adminQuote;
    protected $checkoutSession;

    /**
     * ProcessQuoteObserver constructor.
     * @param FreeGift $helper
     */
    public function __construct(
        FreeGift $giftH,
        Purchase $purchaseH,
        Cart $crt, 
        AdminQuote $adminQ,
        CheckoutSession $checkoutS
    ) {
        $this->giftHelper = $giftH;
        $this->purchaseHelper = $purchaseH;
        
        $this->cart = $crt;
        $this->adminQuote = $adminQ;
        $this->checkoutSession = $checkoutS;
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
        
        $eventName = $observer->getEvent()->getName(); 
        
        
        $freeGift = false;
        if ($this->giftHelper->isEnabledFreeGift()) {
            
            $logger->info('free gift');
            
            $quote = $this->cart->getQuote();
            
            
            $giftSkus = $this->giftHelper->fillGifts($quote);
            
            foreach ($giftSkus as $sku => $qty) {
                $freeGift = true;
                $this->giftHelper->addGiftToQuote($quote, $sku, $qty, false, true, $observer->getEvent()->getName() );
            }
        }

        if (!$freeGift && $this->purchaseHelper->isEnabledPurchase()) {
            
            $logger->info('purchase');
            
           // $quote = $this->cart->getQuote();
            $quote = $this->checkoutSession->getQuote();
            if (trim($quote->getId()) == "") {
          //      $quote = $this->adminQuote->getQuote();
                
                
            }
            
            $logger->info( 'quote id - ' . $quote->getId() );
            $logger->info('admin quote id - ' . $this->adminQuote->getQuote()->getId() );
            
            
            
            $this->purchaseHelper->addGiftToQuote($quote);
        } 
        
        return;
    }
    
}
