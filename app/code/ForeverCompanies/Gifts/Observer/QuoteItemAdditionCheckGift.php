<?php

namespace ForeverCompanies\Gifts\Observer;

use ForeverCompanies\DynamicBundle\Model\Quote\Item;
use ForeverCompanies\Gifts\Helper\Data;
use Magento\Catalog\Model\ProductRepository;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

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
    )
    {
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
            $pendantAttributeSetId = $this->helper->getAttributeSetId('Migration_Pendants');
            $amount = 0;
            $ring = false;
            $diamond = false;
            $giftActivated = false;
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
                    if ($quoteItem->getQty() > 1) {
                        $quoteItem->setDiscountAmount((float)$quoteItem->getProduct()->getPrice());
                        $quote->collectTotals();
                    } else {
                        $quoteItem->setCustomPrice(0);
                        $quoteItem->setPrice(0);
                        $quoteItem->setDiscountAmount($quoteItem->getPrice());
                        $giftMessage = $this->helper->getGiftMessage();
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
                $this->checkRules($quoteItem);
            }
            if ($ring && $diamond && (float)$amount >= (float)$this->helper->getAmountForGift() && !$giftActivated) {
                $this->addGiftToQuote($quote, $this->helper->getGiftProductId(), true);
            }
        }
    }

    protected function checkRules($quoteItem)
    {
        $rules = $this->helper->getRules();
        foreach ($rules as $rule) {
            // THIS WILL BE CHECK RULES, NEED SERIALIZE
        }
    }

    /**
     * @param $quote
     * @param $sku
     * @param false $byId
     */
    private function addGiftToQuote($quote, $sku, $byId = false)
    {
        try {
            if ($byId) {
                $product = $this->productRepository->getById($this->helper->getGiftProductId());
            } else {
                $product = $this->productRepository->get($sku);
            }
            $product->setPrice(0);
            $quote->addProduct($product);
        } catch (NoSuchEntityException $e) {
            return;
        }
    }
}
