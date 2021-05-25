<?php

namespace ForeverCompanies\Gifts\Helper;

use Exception;
use Magento\Catalog\Model\Product;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\LocalizedException;
use Magento\Catalog\Model\ProductRepository;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Serialize\Serializer\Json as Serialize;
use Magento\Quote\Model\Quote\Item;
use Magento\Framework\Stdlib\DateTime;
use Magento\Framework\Message\ManagerInterface;
use Magento\Quote\Model\Quote\Item as QuoteItem;

class FreeGift extends AbstractHelper
{
    /**
     * @var Serialize
     */
    protected $serialize;

    /**
     * @var DateTime
     */
    protected $dateTime;

    /**
     * @var ProductRepository
     */
    protected $productRepository;

    /**
     * @var string[]
     */
    protected $attributeSetIds = [];

    /**
     * @var string[]
     */
    protected $metalTypes = [];

    /**
     * @var string[]
     */
    protected $giftSkus = [];

    /**
     * @var array[]
     */
    protected $rules = [];
    
    protected $messageManager;
    protected $quoteItem;

    /**
     * Data constructor.
     * @param Context $context
     * @param ProductRepository $productRepository
     * @param DateTime $dateTime
     * @param Serialize $serialize
     */
    public function __construct(
        Context $context,
        ProductRepository $productRepository,
        DateTime $dateTime,
        Serialize $serialize,
        ManagerInterface $managerI,
        QuoteItem $quoteI
    ) {
        parent::__construct($context);
        $this->productRepository = $productRepository;
        $this->serialize = $serialize;
        $this->dateTime = $dateTime;
        $this->messageManager = $managerI;
        $this->quoteItem = $quoteI;
        
        $this->fillRules();
    }

    /**
     * @return bool
     */
    public function isEnabledFreeGift()
    {
        return $this->getGiftRules('active');
    }

    /**
     * @return string
     */
    public function isEnabledExpiredTime()
    {
        return $this->getGiftRules('expired');
    }

    /**
     * @return int
     */
    public function getExpiredTime()
    {
        return (int)$this->getGiftRules('time');
    }

    /**
     * @return array
     */
    public function getRules()
    {
        return $this->serialize->unserialize($this->getGiftRules('rules'));
    }

    public function fillRules()
    {
        $this->rules = $this->getRules();
        if (count($this->rules) > 0) {
            foreach ($this->rules as $rule) {
                $this->attributeSetIds[] = $rule['attribute_set_id'];
                $this->metalTypes[] = $rule['metal_type'];
                $this->giftSkus[] = $rule['sku'];
            }
            $this->attributeSetIds = array_unique($this->attributeSetIds);
            $this->metalTypes = array_unique($this->metalTypes);
            $this->giftSkus = array_unique($this->giftSkus);
        }
    }

    /**
     * @return string[]
     */
    public function getSkus()
    {
        if (count($this->giftSkus) == 0) {
            $this->fillRules();
        }
        return $this->giftSkus;
    }

    /**
     * @return string[]
     */
    public function getMetalTypes()
    {
        if (count($this->metalTypes) == 0) {
            $this->fillRules();
        }
        return $this->metalTypes;
    }

    /**
     * @return string[]
     */
    public function getAttributeSetIds()
    {
        if (count($this->attributeSetIds) == 0) {
            $this->fillRules();
        }
        return $this->attributeSetIds;
    }

    /**
     * @return array[]
     */
    public function getFreeGiftRules()
    {
        if (count($this->rules) == 0) {
            $this->fillRules();
        }
        return $this->rules;
    }

    /**
     * @param Item $quoteItem
     * @return string|false
     * @throws NoSuchEntityException
     */
    public function checkRulesInQuoteItem($quoteItem)
    {
        $product = $this->productRepository->getById($quoteItem->getProduct()->getId());
        $metalType = $product->getData('metal_type');
        $attributeSetId = $product->getAttributeSetId();
        if (in_array($metalType, $this->metalTypes) && in_array($attributeSetId, $this->attributeSetIds)) {
            foreach ($this->rules as $rule) {
                if ($metalType == $rule['metal_type'] && $attributeSetId == $rule['attribute_set_id']) {
                    return $rule['sku'];
                }
            }
        }
        return false;
    }

    /**
     * @param $quote
     * @return array
     * @throws Exception
     */
    public function fillGifts($quote)
    {
        $giftSkus = [];
        /** @var \ForeverCompanies\DynamicBundle\Model\Quote\Item $quoteItem */
        foreach ($quote->getAllItems() as $quoteItem) {
            $sku = $quoteItem->getSku();
            if (in_array($sku, $this->getSkus())) {
                continue;
            }
            try {
                $addingSku = $this->checkRulesInQuoteItem($quoteItem);
                if ($addingSku) {
                    $giftSkus = $this->addGiftSku($addingSku, $giftSkus, $quoteItem->getQty());
                }
            } catch (NoSuchEntityException $e) {
                $this->_logger->error($e->getMessage());
            }
        }
        if (count($giftSkus) == 0) {
            return $giftSkus;
        }
        if ($this->isEnabledExpiredTime() && $this->getExpiredTime() !== '0') {
            $now = new \DateTime();
            $expiredTime = $this->getExpiredTime();
        }
        foreach ($quote->getAllItems() as $quoteItem) {
            $sku = $quoteItem->getSku();
            if (isset($giftSkus[$sku]) && isset($now)) {
                if ($quoteItem->getCreatedAt() == null) {
                    $quoteItem->setCreatedAt($this->dateTime->formatDate(true));
                }
                if (isset($expiredTime)) {
                    $createdAt = new \DateTime($quoteItem->getCreatedAt());
                    $quoteExpired = $createdAt->getTimestamp() + $expiredTime;
                    if ($now->getTimestamp() > $quoteExpired) {
                        $quote->removeItem($quoteItem->getItemId());
                        $quote->addMessage('Unfortunately, your free gift has expired.');
                        continue;
                    }
                }
                $quoteItem->setQty($giftSkus[$sku]);
                $quoteItem->addMessage('Free gift added');
                unset($giftSkus[$sku]);
            }
        }
        return $giftSkus;
    }

    /**
     * @param $quote
     * @param string $sku
     * @param int $qty
     * @param bool $byId
     */
    public function addGiftToQuote($quote, $sku, $qty, $byId = false, $update = false, $eventName = false)
    {
        try {
            if ($byId) {
                $product = $this->productRepository->getById($byId);
            } else {
                $product = $this->productRepository->get($sku);
            }
            if ($update) {
                foreach ($quote->getAllItems() as $_quoteItem) {
                    if ($_quoteItem->getProduct()->getSku() == $sku) {
                        $this->quoteItem->load($_quoteItem->getItemId())->delete();
                    }
                }
            }
            $product->setPrice(0);
            $product->setQty($qty);
            $quote->addProduct($product);
            $quote->save();
            if ($eventName == "free_gift_add_logic") {
                $this->messageManager->addSuccessMessage(__("A free gift has been added to your order!"));
            }
        } catch (NoSuchEntityException $e) {
            return;
        }
    }

    /**
     * @param string $sku
     * @param array $giftSkus
     * @param float $qty
     * @return array
     */
    protected function addGiftSku($sku, $giftSkus, $qty)
    {
        if (isset($giftSkus[$sku])) {
            $giftSkus[$sku] += $qty;
        } else {
            $giftSkus[$sku] = $qty;
        }
        return $giftSkus;
    }

    /**
     * @param string $config
     * @return string
     */
    protected function getGiftRules(string $config)
    {
        return $this->scopeConfig->getValue('forevercompanies_gifts/free_gift_rules/' . $config);
    }
}
