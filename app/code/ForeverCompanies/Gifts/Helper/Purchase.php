<?php

namespace ForeverCompanies\Gifts\Helper;

use Magento\Catalog\Model\Product;
use Magento\Eav\Model\Config;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\LocalizedException;

use Magento\Framework\Message\ManagerInterface;
use Magento\Catalog\Model\ProductRepository;
use Magento\Quote\Model\Quote\Item as QuoteItem;

class Purchase extends AbstractHelper
{
    /**
     * @var CollectionFactory
     */
    protected $attributeSetCollectionFactory;

    /**
     * @var Config
     */
    protected $eav;
    
    protected $productRepository;
    protected $messageManager;
    protected $quoteItem;

    /**
     * Data constructor.
     * @param Context $context
     * @param CollectionFactory $attributeSetCollectionFactory
     * @param Config $eav
     */
    public function __construct(
        Context $context,
        CollectionFactory $attributeSetCollectionFactory,
        Config $eav,
        ProductRepository $productR,
        ManagerInterface $managerI,
        QuoteItem $quoteI
    ) {
        parent::__construct($context);
        $this->eav = $eav;
        $this->attributeSetCollectionFactory = $attributeSetCollectionFactory;
        $this->productRepository = $productR;
        $this->messageManager = $managerI;
        $this->quoteItem = $quoteI;
    }
    
    public function addGiftToQuote($quote)
    {
        $sku = $this->scopeConfig->getValue('forevercompanies_gifts/purchase/product_id'); 
        $product = $this->productRepository->get($sku);
        
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/giftlog.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        $logger->info( 'add gift to quote' );
        
        $setIdFound = false;
        foreach ($quote->getAllItems() as $_quoteItem) {
            
            $logger->info( 'quote item id ' . $_quoteItem->getId() );
            $logger->info( 'product sku - ' . $product->getSku() );
            
            
            $_quoteItem->setSetId(42);
     //       $setIdFound = true;
            
            if ($_quoteItem->getSetId() > 0) {
                $setIdFound = true;
            }
              
            if ($_quoteItem->getProduct()->getSku() == $sku) {
                $this->quoteItem->load($_quoteItem->getItemId())->delete();
                
                
                
                //  unset($_quoteItem);
            }
        }
        
        if ($setIdFound && $quote->getSubtotal() >= $this->scopeConfig->getValue('forevercompanies_gifts/purchase/total')) {
                
            $logger->info( 'in' );
            
            $product->setPrice(0);
            $product->setQty(1);
            $quote->addProduct($product);
            
            $quote->setTotalsCollectedFlag(true);
        //    $quote->collectTotals();
            
            $quote->save();
            $this->messageManager->addSuccessMessage(__($this->scopeConfig->getValue('forevercompanies_gifts/purchase/message')));
            
        }
    }
    

    /**
     * @return bool
     */
    public function isEnabledPurchase()
    {
        return $this->getPurchaseConfig('active');
    }

    /**
     * @param $name
     * @return string|null
     */
    public function getAttributeSetId($name)
    {
        $set = $this->attributeSetCollectionFactory->create()->addFieldToFilter('attribute_set_name', $name);
        return $set->getFirstItem()->getData('attribute_set_id');
    }

    /**
     * @return string
     */
    public function getGiftProductId()
    {
        return $this->getPurchaseConfig('product_id');
    }

    /**
     * @return string
     */
    public function getAmountForGift()
    {
        return $this->getPurchaseConfig('total');
    }

    /**
     * @return string
     */
    public function getGiftMessage()
    {
        return $this->getPurchaseConfig('message');
    }

    /**
     * @return string
     */
    public function getGiftLink()
    {
        return $this->getPurchaseConfig('link');
    }

    /**
     * @param string $config
     * @return string
     */
    protected function getPurchaseConfig(string $config)
    {
        return $this->scopeConfig->getValue('forevercompanies_gifts/purchase/' . $config);
    }

    /**
     * @param $attribute
     * @param $id
     * @return bool|string
     */
    public function getValue($attribute, $id)
    {
        try {
            $source = $this->eav->getAttribute(Product::ENTITY, $attribute)->getSource();
            return $source->getOptionText($id);
        } catch (LocalizedException $e) {
            return false;
        }
    }
    
    public function fillGifts($quote)
    {
        
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
                        $quote->addMessage('Free gift remove. Time was expired');
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
}
