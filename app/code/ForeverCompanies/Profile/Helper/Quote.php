<?php

namespace ForeverCompanies\Profile\Helper;
 
use Magento\Checkout\Model\Cart;
 
class Quote
{
	public $cart;
	public $quoteRepository;
	
		public function __construct(
			Cart $cart,
			\Magento\Quote\Api\CartRepositoryInterface $quoteRepository
		) {
			$this->cart = $cart;
			$this->quoteRepository = $quoteRepository;
		}
	
	public function getQuoteId()
	{
		// get the quote id
		$quoteId = $this->cart->getQuote()->getId();
		
		if(!$quoteId > 0) {
			// save the quote to create an instance
			$this->cart->saveQuote();
			
			// fetch the card id again
			$quoteId = $this->cart->getQuote()->getId();
		}
		
		return $quoteId;
	}
	
	public function getQuote($quoteId = 0)
	{
		// load the quote using quote repository
		return $this->quoteRepository->get($quoteId);
	}
	
	public function clear()
	{
		$this->cart->truncate();
	}
	
	/**
	* get last cart item added
	*/
	public function getLastQuoteItemId($quoteId = 0)
	{
		$_objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$collecion = $_objectManager->create('Magento\Quote\Model\ResourceModel\Quote\Item\Collection')->addFieldToFilter('quote_id',$quoteId);
		
		$lastitem = $collecion->getLastItem();
		
		return $lastitem->getId();
	}
}