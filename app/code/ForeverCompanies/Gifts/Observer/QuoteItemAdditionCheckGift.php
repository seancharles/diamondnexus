<?php

namespace ForeverCompanies\Gifts\Observer;

use Exception;
use ForeverCompanies\DynamicBundle\Model\Quote\Item;
use ForeverCompanies\Gifts\Helper\Purchase;
use ForeverCompanies\Gifts\Helper\FreeGift;
use Magento\Catalog\Model\ProductRepository;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Model\Quote;
use Magento\Framework\Message\ManagerInterface;

use Magento\Checkout\Model\Cart;

use Magento\Checkout\Model\Session as CheckoutSession;

class QuoteItemAdditionCheckGift implements ObserverInterface
{
    /**
     * @var Purchase
     */
    protected $purchaseHelper;

    /**
     * @var FreeGift
     */
    protected $freeGiftHelper;

    /**
     * @var ProductRepository
     */
    protected $productRepository;
    
    private $checkoutSession;
    protected $cart;

    /**
     * QuoteItemAdditionCheckGift constructor.
     * @param Purchase $purchaseHelper
     * @param FreeGift $freeGiftHelper
     * @param ProductRepository $productRepository
     */
    public function __construct(
        Purchase $purchaseHelper,
        FreeGift $freeGiftHelper,
        ProductRepository $productRepository,
        CheckoutSession $checkoutSession,
        Cart $crt
    ) {
        $this->purchaseHelper = $purchaseHelper;
        $this->freeGiftHelper = $freeGiftHelper;
        $this->productRepository = $productRepository;
        $this->checkoutSession = $checkoutSession;
        $this->cart = $crt;
        
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/giftlog.log');
        $this->logger = new \Zend\Log\Logger();
        $this->logger->addWriter($writer);
     
    }

    
    /**
     * {@inheritdoc}
     * @throws Exception
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        return; //nope
        if ($this->purchaseHelper->isEnabledPurchase()) {
           
            $this->purchaseProcess($quoteItem);
            
        } elseif ($this->freeGiftHelper->isEnabledFreeGift()) {
            return $this->freeGiftProcess();
        }
    }

    /**
     * @param Quote $quote
     * @throws Exception
     */
    protected function freeGiftProcess()
    {
    //    $quote = $this->checkoutSession->getQuote();
        $quote = $this->cart->getQuote();
        $giftSkus = $this->freeGiftHelper->fillGifts($quote);
        
        $ret = false;
        if (!empty($giftSkus)) {
            $ret = true;
            foreach ($giftSkus as $sku => $qty) {
                $this->freeGiftHelper->addGiftToQuote($quote, $sku, $qty);
            }
            $quote->collectTotals();
        }
        return $ret;
    }

    /**
     * @param Quote $quote
     */
    protected function purchaseProcess($quote)
    {
        $ringAttributeSetId = $this->purchaseHelper->getAttributeSetId('Migration_Ring Settings');
        $diamondAttributeSetId = $this->purchaseHelper->getAttributeSetId('Migration_Loose Diamonds');
        $amount = 0;
        $ring = false;
        $diamond = false;
        $giftActivated = false;
        /** @var Item $quoteItem */
        foreach ($quote->getAllItems() as $quoteItem) {
            if ($quoteItem->getProduct()->getId() == $this->purchaseHelper->getGiftProductId()) {
                if ($quoteItem->getQty() > 1) {
                    $quoteItem->setDiscountAmount((float)$quoteItem->getProduct()->getPrice());
                    $quote->collectTotals();
                } else {
                    $quoteItem->setCustomPrice(0);
                    $quoteItem->setPrice(0);
                    $quoteItem->setDiscountAmount($quoteItem->getPrice());
                    $giftMessage = $this->purchaseHelper->getGiftMessage();
                    $quoteItem->addMessage($giftMessage);
                    $quote->collectTotals();
                }
                $giftActivated = true;
                continue;
            }
            $amount += $quoteItem->getPrice();
            $quoteItemAttributeSetId = $quoteItem->getProduct()->getAttributeSetId();
            if ($diamondAttributeSetId == $quoteItemAttributeSetId) {
                $diamond = true;
            }
            if ($ringAttributeSetId == $quoteItemAttributeSetId) {
                $ring = true;
            }
        }
        $amountForGift = (float)$this->purchaseHelper->getAmountForGift();
        if ($ring && $diamond && (float)$amount >= $amountForGift && !$giftActivated) {
            $this->freeGiftHelper->addGiftToQuote($quote, $this->purchaseHelper->getGiftProductId(), 1, true);
        }
    }
}
