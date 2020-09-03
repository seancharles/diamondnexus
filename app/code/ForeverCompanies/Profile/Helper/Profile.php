<?php

namespace ForeverCompanies\Profile\Helper;
 
use Magento\Checkout\Model\Cart;
 
class Profile
{
		protected $customerSession;
		protected $formKey;
		protected $cart;
		
		protected $profile = [
			'form_key' => null,
			'customer_id' => 0,
			'logged_in' => false,
			'lastsync' => null,
			'cart_items' => null,
			'cart_qty' => 0
		];
		
		public function __construct(
			\Magento\Customer\Model\Session $customerSession,
			\Magento\Framework\Data\Form\FormKey $formKey,
			Cart $cart,
			\Magento\Backend\App\Action\Context $context
		) {
			$this->customerSession = $customerSession;
			$this->formKey = $formKey;
			$this->cart = $cart;
			
			$this->setProfileKey('form_key', $this->formKey->getFormKey());
			$this->setProfileKey('customer_id', (int) $customerSession->getCustomerId());
			$this->setProfileKey('logged_in', (bool) $customerSession->isLoggedIn());
			
			// add cart into to 
			if($this->cart) {
				$items = [];
				
				foreach($cart->getItems() as $item) {
					$items[] = [
						'name' => $item->getName(),
						'sku' => $item->getSku(),
						'price' => $item->getPrice()
					];
				}
				$this->setProfileKey('cart_items', $items);
				$this->setProfileKey('quote_id', (int) $this->cart->getQuote()->getId());
				$this->setProfileKey('cart_qty', (int) $cart->getItemsCount());
			} else {
				$this->setProfileKey('cart_items', null);
				$this->setProfileKey('cart_qty', 0);
			}
		}
	
	public function sync()
	{
		$now = time();
		
		$this->customerSession->setLastSync($now);
		
		$this->setProfileKey('lastsync', $now);
	}
	
	public function getProfile()
	{
		return $this->profile;
	}

	public function getProfileKey($key = null)
	{
		return $this->profile[$key];
	}
	
	public function setProfileKey($key = null, $value = null)
	{
		$this->profile[$key] = $value;
	}
}