<?php

	namespace ForeverCompanies\Profile\Controller\Cart;

	class Remove extends \ForeverCompanies\Profile\Controller\ApiController
	{
		protected $profileHelper;
		protected $quoteHelper;
		
		public function __construct(
			\ForeverCompanies\Profile\Helper\Profile $profileHelper,
			\ForeverCompanies\Profile\Helper\Quote $quoteHelper,
			\Magento\Backend\App\Action\Context $context
		) {
			$this->profileHelper = $profileHelper;
			$this->quoteHelper = $quoteHelper;
			parent::__construct($context);
		}

		public function execute()
		{
			$result = [
				'success' => false
			];
			
			$itemsList = array();
			
			try {
				$post = $this->profileHelper->getPost();
				
				if ($this->profileHelper->formKeyValidator->validate($this->getRequest())) {
					
					$itemPost = $post->item_list;
					
					// convert post object to array;
					foreach($itemPost as $item) {
						$itemsList[$item] = $item;
					}
					
					if(count($itemsList) > 0){
						// get the quote id
						$quoteId = $this->quoteHelper->getQuoteId();
						
						// load the quote using quote repository
						$quote = $this->quoteHelper->getQuote($quoteId);
						
						// get the cart items
						$quoteItems = $quote->getItems();
						
						// iterate the users cart items
						foreach($quoteItems as $item)
						{
							if(in_array($item->getItemId(), $itemsList) == true)
							{
								$item->delete();
							}
						}
						
						$result['message'] = 'Removed item(s) from cart';
						$result['success'] = true;
						
					} else {
						$result['message'] = 'Unable to find cart item';
					}
					
					// updates the last sync time
					$this->profileHelper->sync();
					
					$result['profile'] = $this->profileHelper->getProfile();
					
				} else {
					$result['success'] = false;
					$result['message'] = 'Invalid form key.';
				}
			
			} catch (\Exception $e) {
				$result['message'] = $e->getMessage();
			}
			
			print_r(json_encode($result));
		}
	}