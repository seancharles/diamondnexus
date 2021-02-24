<?php

namespace ForeverCompanies\Gifts\Observer;

use ForeverCompanies\DynamicBundle\Model\Quote\Item;
use ForeverCompanies\Gifts\Helper\Data;
use Magento\Catalog\Model\ProductRepository;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;

class QuoteItemAdditionCheckGift implements ObserverInterface
{
    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var ProductRepository
     */
    protected $productRepository;

    /**
     * QuoteItemAdditionCheckGift constructor.
     * @param Data $helper
     * @param ProductRepository $productRepository
     */
    public function __construct(
        Data $helper,
        ProductRepository $productRepository
    ) {
        $this->helper = $helper;
        $this->productRepository = $productRepository;
    }

    /**
     * {@inheritdoc}
     * @throws LocalizedException
     */
    public function execute(Observer $observer)
    {
        /** @see \Magento\Quote\Model\Quote::addProduct() */

        if ($this->helper->isEnabled()) {
            $ringAttributeSetId = $this->helper->getAttributeSetId('Migration_Ring Settings');
            $diamondAttributeSetId = $this->helper->getAttributeSetId('Migration_Loose Diamonds');
            $amount = 0;
            $ring = false;
            $diamond = false;
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
            /** @var Item $quoteItem */
            foreach ($quote->getAllItems() as $quoteItem) {
                if ($quoteItem->getProduct()->getId() == $this->helper->getGiftProductId()) {
                    $quoteItem->setCustomPrice(0);
                    $quoteItem->setPrice(0);
                    $quoteItem->setDiscountAmount($quoteItem->getPrice());
                    $giftMessage = $this->helper->getGiftMessage();
                    $quoteItem->addMessage($giftMessage);
                    $quote->collectTotals();
                    return;
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
            if ($ring && $diamond && (float)$amount >= (float)$this->helper->getAmountForGift()) {
                $product = $this->productRepository->getById($this->helper->getGiftProductId());
                $product->setPrice(0);
                $quote->addProduct($product);
            }
        }
    }
}
