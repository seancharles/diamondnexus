<?php

namespace ForeverCompanies\Gifts\Observer;

use Exception;
use ForeverCompanies\Gifts\Helper\FreeGift;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Model\Quote;

class ProcessQuoteObserver implements ObserverInterface
{
    /**
     * @var FreeGift
     */
    protected $helper;

    /**
     * ProcessQuoteObserver constructor.
     * @param FreeGift $helper
     */
    public function __construct(
        FreeGift $helper
    ) {
        $this->helper = $helper;
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
        /** @see \Magento\Quote\Model\Quote\TotalsCollector::collect */

        /**
         * @var $quote Quote
         */
        $quote = $observer->getData('quote');
        $giftSkus = $this->helper->fillGifts($quote);
        foreach ($giftSkus as $sku => $qty) {
            $this->helper->addGiftToQuote($quote, $sku, $qty);
        }
    }
}
