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

class ProcessQuoteObserver implements ObserverInterface
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
        $eventName = $observer->getEvent()->getName();
        $freeGift = false;
        if ($this->giftHelper->isEnabledFreeGift()) {
            
            $quote = $this->cart->getQuote();
            $giftSkus = $this->giftHelper->fillGifts($quote);
            
            foreach ($giftSkus as $sku => $qty) {
                $freeGift = true;
                $this->giftHelper->addGiftToQuote($quote, $sku, $qty, false, true, $observer->getEvent()->getName());
            }
        }

        if (!$freeGift && $this->purchaseHelper->isEnabledPurchase()) {
            $quote = $this->cart->getQuote();
            $this->purchaseHelper->addGiftToQuote($quote);
        }
        return $this;
    }
}
