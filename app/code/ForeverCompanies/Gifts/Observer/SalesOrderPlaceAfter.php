<?php

namespace ForeverCompanies\Gifts\Observer;

use Exception;
use ForeverCompanies\Gifts\Helper\FreeGift;
use ForeverCompanies\Gifts\Helper\Purchase;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Model\QuoteFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;

class SalesOrderPlaceAfter implements ObserverInterface
{
    /**
     * @var FreeGift
     */
    protected $giftHelper;
    protected $purchaseHelper;
    protected $quoteFactory;
    
    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    protected $orderRepository;
    
    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;
    
    /**
     * @var \Magento\Quote\Api\Data\CartItemInterfaceFactory
     */
    protected $cartItemFactory;
    
    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    protected $quoteRepository;
    
    /**
     * @var \Magento\Sales\Model\Order\ItemFactory
     */
    protected $orderItemFactory;
    protected $scopeConfig;
    protected $storeScope;
    
    /**
     * ProcessQuoteObserver constructor.
     * @param FreeGift $helper
     */
    public function __construct(
        FreeGift $giftH,
        Purchase $purchaseH,
        QuoteFactory $quoteF,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Quote\Api\Data\CartItemInterfaceFactory $cartItemFactory,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Magento\Sales\Model\Order\ItemFactory $orderItemFactory,
        ScopeConfigInterface $scopeConfig
    ) {
        
        $this->giftHelper = $giftH;
        $this->purchaseHelper = $purchaseH;
        $this->quoteFactory = $quoteF;
        $this->productRepository    = $productRepository;
        $this->orderRepository      = $orderRepository;
        $this->cartItemFactory      = $cartItemFactory;
        $this->quoteRepository      = $quoteRepository;
        $this->orderItemFactory     = $orderItemFactory;
        $this->scopeConfig = $scopeConfig;
        $this->storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
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
        $this->_addFreeItemToOrder($observer->getEvent()->getOrder());
        return $this;
    }
    
    protected function _addFreeItemToOrder($order)
    {
        $freeItem = false;
        $quote = $this->quoteRepository->get($order->getQuoteId());
        if ($this->giftHelper->isEnabledFreeGift()) {
            $giftSkus = $this->giftHelper->fillGifts($quote);
            
            if (count($giftSkus) > 0) {
                $freeItem = true;
                foreach ($giftSkus as $sku => $qty) {
                    $freeSku = $sku;
                    break;
                }
            }
        }
            
        if (!$freeItem && $this->purchaseHelper->isEnabledPurchase()) {
            foreach ($quote->getAllItems() as $_quoteItem) {
                
                // this needs to be set for purchase to work
            //    $_quoteItem->setSetId(42);
                
                if ($_quoteItem->getSetId() > 0) {
                    if ($quote->getSubtotal() >=
                            $this->scopeConfig->getValue('forevercompanies_gifts/purchase/total', $this->storeScope)) {
                        $freeItem = true;
                        $freeSku =
                            $this->scopeConfig->getValue(
                                'forevercompanies_gifts/purchase/product_id',
                                $this->storeScope
                            );
                        break;
                    }
                }
            }
        }
        
        if (!$freeItem) {
            return;
        }
        
        $product = $this->productRepository->get($freeSku);
        
        $quoteItem = $this->cartItemFactory->create();
        $quoteItem->setProduct($product);
        $quoteItem->setCustomPrice(0);
        $quoteItem->setOriginalCustomPrice($product->getPrice());
        $quoteItem->getProduct()->setIsSuperMode(true);
        $quote->addItem($quoteItem);
        $quote->save();
        
        $orderItem = $this->orderItemFactory->create();
        $orderItem
        ->setStoreId($order->getStoreId())
        ->setQuoteItemId($quoteItem->getId())
        ->setProductId($product->getId())
        ->setProductType($product->getTypeId())
        ->setName($product->getName())
        ->setSku($product->getSku())
        ->setQtyOrdered(1)
        ->setPrice(0)
        ->setBasePrice(0)
        ->setOriginalPrice($product->getPrice())
        ->setBaseOriginalPrice($product->getPrice())
        ->setPriceInclTax(0)
        ->setBasePriceInclTax(0)
        ->setRowTotal(0)
        ->setBaseRowTotal(0)
        ->setRowTotalInclTax(0)
        ->setBaseRowTotalInclTax(0)
        ->setWeight(1)
        ->setIsVirtual(1);
        $order->addItem($orderItem);
        
        $order->setTotalItemCount($order->getTotalItemCount() + 1);
        $order->setTotalQtyOrdered($order->getTotalQtyOrdered() + 1);
        $this->orderRepository->save($order);
    }
}
