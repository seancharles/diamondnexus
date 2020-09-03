<?php

	namespace ForeverCompanies\Profile\Controller\Sync;

	class RemoveCartItem extends \ForeverCompanies\Profile\Controller\Sync\ApiController
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
			
			try {
				// parse the json post
				$json = file_get_contents('php://input');

				// Converts it into a PHP object
				$post = json_decode($json);
				
				$itemId = $post->item_id;
				
				if($itemId > 0)
				{
					// get the quote id
					$quoteId = $this->quoteHelper->getQuoteId();
					
					// load the quote using quote repository
					$quote = $this->quoteHelper->getQuote($quoteId);
					
					// get the cart items
					$items = $quote->getItems();
					
					// iterate the users cart items
					foreach($items as $item)
					{
						if($item->getId() == $itemId)
						{
							$item->delete();
						}
					}
					
					$result['success'] = true;
					
				} else {
					$result['message'] = 'Unable to find cart item';
					$result['success'] = false;
				}
				
				// updates the last sync time
				$this->profileHelper->sync();
				
				$result['profile'] = $this->profileHelper->getProfile();
			
			} catch (\Exception $e) {
				$result['message'] = $e->getMessage();
			}
			
			print_r(json_encode($result));
		}
	}