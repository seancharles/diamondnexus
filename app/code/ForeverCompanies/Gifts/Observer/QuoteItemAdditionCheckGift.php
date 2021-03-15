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

    /**
     * QuoteItemAdditionCheckGift constructor.
     * @param Purchase $purchaseHelper
     * @param FreeGift $freeGiftHelper
     * @param ProductRepository $productRepository
     */
    public function __construct(
        Purchase $purchaseHelper,
        FreeGift $freeGiftHelper,
        ProductRepository $productRepository
    ) {
        $this->purchaseHelper = $purchaseHelper;
        $this->freeGiftHelper = $freeGiftHelper;
        $this->productRepository = $productRepository;
    }

    /**
     * {@inheritdoc}
     * @throws Exception
     */
    public function execute(Observer $observer)
    {
        /** @see \Magento\Quote\Model\Quote::addProduct() */
        if ($this->purchaseHelper->isEnabledPurchase() || $this->freeGiftHelper->isEnabledFreeGift()) {
            /** @var Item $quoteItem */
            foreach ($observer->getData('items') as $quoteItem) {
                if (!isset($quote)) {
                    $quote = $quoteItem->getQuote();
                    break;
                }
            }
            if (!isset($quote)) {
                return;
            }
            if ($this->purchaseHelper->isEnabledPurchase()) {
                $this->purchaseProcess($quote);
            }
            if ($this->freeGiftHelper->isEnabledFreeGift()) {
                $this->freeGiftProcess($quote);
            }
        }
    }

    /**
     * @param Quote $quote
     * @throws Exception
     */
    protected function freeGiftProcess($quote)
    {
        $giftSkus = $this->freeGiftHelper->fillGifts($quote);
        foreach ($giftSkus as $sku => $qty) {
            $this->freeGiftHelper->addGiftToQuote($quote, $sku, $qty);
        }
        $quote->collectTotals();
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
