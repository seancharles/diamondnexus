<?php

namespace ForeverCompanies\Gifts\Observer;

use ForeverCompanies\DynamicBundle\Model\Quote\Item;
use ForeverCompanies\Gifts\Helper\FreeGift;
use ForeverCompanies\Gifts\Helper\Purchase;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Message\ManagerInterface;
use Magento\Quote\Model\Quote;

class QuoteItemRemoveCheckGift implements ObserverInterface
{
    /**
     * @var FreeGift
     */
    protected $freeGiftHelper;

    /**
     * @var Purchase
     */
    protected $purchaseHelper;

    /**
     * @var ManagerInterface
     */
    protected $messageManager;

    /**
     * QuoteItemRemoveCheckGift constructor.
     * @param FreeGift $freeGiftHelper
     * @param Purchase $purchaseHelper
     * @param ManagerInterface $messageManager
     */
    public function __construct(
        FreeGift $freeGiftHelper,
        Purchase $purchaseHelper,
        ManagerInterface $messageManager
    ) {
        $this->freeGiftHelper = $freeGiftHelper;
        $this->purchaseHelper = $purchaseHelper;
        $this->messageManager = $messageManager;
    }

    /**
     * {@inheritdoc}
     * @throws LocalizedException
     */
    public function execute(Observer $observer)
    {
        /** @see \Magento\Quote\Model\Quote::removeItem() */

        /** @var Quote\Item|null $quoteItem */
        $quoteItem = $observer->getData('quote_item');
        if ($quoteItem == null) {
            return;
        }
        $quote = $quoteItem->getQuote();
        if ($quote == null) {
            return;
        }

        if ($this->purchaseHelper->isEnabledPurchase()) {
            $this->processPurchase($quote);
        }
        if ($this->freeGiftHelper->isEnabledFreeGift()) {
            $this->processFreeGift($quote, $quoteItem);
        }
    }

    /**
     * @param Quote $quote
     * @param Quote\Item $quoteItem
     * @throws LocalizedException
     */
    protected function processFreeGift($quote, $quoteItem)
    {
        if (in_array($quoteItem->getSku(), $this->freeGiftHelper->getSkus())) {
            return;
        }
        $removingSku = $this->freeGiftHelper->checkRulesInQuoteItem($quoteItem);
        if ($removingSku) {
            $qty = $quoteItem->getQty();
            /** @var Quote\Item $item */
            foreach ($quote->getAllItems() as $item) {
                if ($item->getSku() == $removingSku) {
                    if ($item->getQty() <= $qty) {
                        $quote->removeItem($item->getItemId());
                    } else {
                        $item->setQty($item->getQty() - $qty);
                        $quote->addItem($item);
                    }
                }
            }
        }
    }

    /**
     * @param Quote $quote
     */
    protected function processPurchase($quote)
    {
        $ringAttributeSetId = $this->purchaseHelper->getAttributeSetId('Migration_Ring Settings');
        $diamondAttributeSetId = $this->purchaseHelper->getAttributeSetId('Migration_Loose Diamonds');
        $amount = 0;
        $ring = false;
        $diamond = false;

        /** @var Item $quoteItem */
        foreach ($quote->getAllItems() as $key => $quoteItem) {
            if ($quoteItem->getProduct()->getId() == $this->purchaseHelper->getGiftProductId()) {
                $productIndex = $key;
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
        if (!$ring || !$diamond || (float)$amount < (float)$this->purchaseHelper->getAmountForGift()) {
            if (isset($productIndex)) {
                $this->messageManager->addErrorMessage('Free gift is deleted from your order');
                $quote->deleteItem($quote->getAllItems()[$productIndex]);
            }
        }
    }
}
