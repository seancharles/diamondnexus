<?php

namespace ForeverCompanies\Gifts\Observer;

use ForeverCompanies\DynamicBundle\Model\Quote\Item;
use ForeverCompanies\Gifts\Helper\Data;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Message\ManagerInterface;

class QuoteItemRemoveCheckGift implements ObserverInterface
{
    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var ManagerInterface
     */
    protected $messageManager;

    /**
     * QuoteItemRemoveCheckGift constructor.
     * @param Data $helper
     * @param ManagerInterface $messageManager
     */
    public function __construct(
        Data $helper,
        ManagerInterface $messageManager
    ) {
        $this->helper = $helper;
        $this->messageManager = $messageManager;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(Observer $observer)
    {
        /** @see \Magento\Quote\Model\Quote::removeItem() */

        if ($this->helper->isEnabled()) {
            $ringAttributeSetId = $this->helper->getAttributeSetId('Migration_Ring Settings');
            $diamondAttributeSetId = $this->helper->getAttributeSetId('Migration_Loose Diamonds');
            $amount = 0;
            $ring = false;
            $diamond = false;
            /** @var \Magento\Quote\Model\Quote\Item|null $quoteItem */
            $quoteItem = $observer->getData('quote_item');
            if ($quoteItem == null) {
                return;
            }
            $quote = $quoteItem->getQuote();
            if ($quote == null) {
                return;
            }
            /** @var Item $quoteItem */
            foreach ($quote->getAllItems() as $key => $quoteItem) {
                if ($quoteItem->getProduct()->getId() == $this->helper->getGiftProductId()) {
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
            if (!$ring || !$diamond || (float)$amount < (float)$this->helper->getAmountForGift()) {
                if (isset($productIndex)) {
                    $this->messageManager->addErrorMessage('Free gift is deleted from your order');
                    $quote->deleteItem($quote->getAllItems()[$productIndex]);
                }
            }
        }
    }
}
