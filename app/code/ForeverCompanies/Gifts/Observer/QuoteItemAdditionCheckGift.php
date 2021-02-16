<?php

namespace ForeverCompanies\Gifts\Observer;

use ForeverCompanies\DynamicBundle\Model\Quote\Item;
use ForeverCompanies\Gifts\Helper\Data;
use Magento\Framework\Event\ObserverInterface;

class QuoteItemAdditionCheckGift implements ObserverInterface
{
    /**
     * @var Data
     */
    protected $helper;

    public function __construct(
        Data $helper
    ) {
        $this->helper = $helper;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /** @see \Magento\Quote\Model\Quote::addProduct() */
        /** Check Migration_Ring Settings */
        /**
         * total amount 2500+
        AND
        item with attribute_set = Migration_Ring Settings
        AND
        item with attribute_set = Migration_Loose Diamonds
         **/

        if ($this->helper->isEnabled()) {
            $ringAttributeSetId = $this->helper->getAttributeSetId('Migration_Ring Settings');
            $diamondAttributeSetId = $this->helper->getAttributeSetId('Migration_Loose Diamonds');
            $amount = 0;
            $isGiftHere = false;
            $isGiftMustBeHere = false;
            $ring = false;
            $diamond = false;
            /** @var Item $quoteItem */
            foreach ($observer->getData('items') as $quoteItem) {
                $test = 1; /** For xDebug */
                /** Check here work rules and add new product in quote */
            }
        }
    }
}
