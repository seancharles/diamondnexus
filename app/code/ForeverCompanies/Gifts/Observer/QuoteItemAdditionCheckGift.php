<?php

namespace ForeverCompanies\Gifts\Observer;

use Exception;
use ForeverCompanies\DynamicBundle\Model\Quote\Item;
use ForeverCompanies\Gifts\Helper\Data;
use Magento\Catalog\Model\ProductRepository;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Model\Quote;

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

    protected $attributeSetIds;

    protected $metalTypes;

    protected $giftSkus;

    protected $rules;

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
     * @throws Exception
     */
    public function execute(Observer $observer)
    {
        /** @see \Magento\Quote\Model\Quote::addProduct() */
        if ($this->helper->isEnabledPurchase() || $this->helper->isEnabledFreeGift()) {
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
            if ($this->helper->isEnabledPurchase()) {
                $this->purchaseProcess($quote);
            }
            if ($this->helper->isEnabledFreeGift()) {
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
        $this->fillRules();
        $giftSkus = [];
        /** @var Item $quoteItem */
        foreach ($quote->getAllItems() as $quoteItem) {
            $sku = $quoteItem->getSku();
            if (in_array($sku, $this->giftSkus)) {
                $this->addGiftSku($sku, $giftSkus);
                continue;
            }
            $metalType = $quoteItem->getProduct()->getData('metal_type');
            $attributeSetId = $quoteItem->getProduct()->getAttributeSetId();
            if (in_array($metalType, $this->metalTypes) && in_array($attributeSetId, $this->attributeSetIds)) {
                foreach ($this->rules as $rule) {
                    if ($metalType == $rule['metal_type'] && $attributeSetId == $rule['attribute_set_id']) {
                        $this->addGiftSku($rule['sku'], $giftSkus);
                        break;
                    }
                }
            }
        }
        if (count($giftSkus) == 0) {
            return;
        }
        if ($this->helper->isEnabledExpiredTime() && $this->helper->getExpiredTime() !== '0') {
            $now = new \DateTime();
            $expiredTime = $this->helper->getExpiredTime();
        }
        foreach ($quote->getAllItems() as $id => $quoteItem) {
            $sku = $quoteItem->getSku();
            if (isset($giftSkus[$sku]) && isset($now)) {
                if (isset($expiredTime)) {
                    $createdAt = new \DateTime($quote->getCreatedAt());
                    $quoteExpired = $createdAt->getTimestamp() + $expiredTime;
                    if ($now->getTimestamp() > $quoteExpired) {
                        $quote->removeItem($id);
                        $quote->addMessage('Free gift remove. Time was expired');
                        continue;
                    }
                }
            }
            $quoteItem->setQty($giftSkus[$sku]);
            unset($giftSkus[$sku]);
            $changeQty = 1;
        }
        if (count($giftSkus) == 0) {
            if (isset($changeQty)) {
                $quote->collectTotals();
            }
            return;
        }
        foreach ($giftSkus as $sku => $qty) {
            $this->addGiftToQuote($quote, $sku, $qty);
        }
        $quote->collectTotals();
    }

    /**
     * @param string $sku
     * @param array $giftSkus
     */
    private function addGiftSku($sku, $giftSkus)
    {
        if (isset($giftSkus[$sku])) {
            $giftSkus[$sku]++;
        } else {
            $giftSkus[$sku] = 1;
        }
    }

    /**
     * @param Quote $quote
     */
    protected function purchaseProcess($quote)
    {
        $ringAttributeSetId = $this->helper->getAttributeSetId('Migration_Ring Settings');
        $diamondAttributeSetId = $this->helper->getAttributeSetId('Migration_Loose Diamonds');
        $amount = 0;
        $ring = false;
        $diamond = false;
        $giftActivated = false;
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
        }
        if ($ring && $diamond && (float)$amount >= (float)$this->helper->getAmountForGift() && !$giftActivated) {
            $this->addGiftToQuote($quote, $this->helper->getGiftProductId(), 1, true);
        }
    }

    /**
     *
     */
    private function fillRules()
    {
        $this->rules = $this->helper->getRules();
        foreach ($this->rules as $rule) {
            $this->attributeSetIds[] = $rule['attribute_set_id'];
            $this->metalTypes[] = $rule['metal_type'];
            $this->giftSkus[] = $rule['sku'];
        }
        $this->attributeSetIds = array_unique($this->attributeSetIds);
        $this->metalTypes = array_unique($this->metalTypes);
        $this->giftSkus = array_unique($this->giftSkus);
    }

    /**
     * @param $quote
     * @param string $sku
     * @param int $qty
     * @param bool $byId
     */
    private function addGiftToQuote($quote, $sku, $qty, $byId = false)
    {
        try {
            if ($byId) {
                $product = $this->productRepository->getById($this->helper->getGiftProductId());
            } else {
                $product = $this->productRepository->get($sku);
            }
            $product->setPrice(0);
            $product->setQty($qty);
            $quote->addProduct($product);
        } catch (NoSuchEntityException $e) {
            return;
        }
    }
}
