<?php

	namespace ForeverCompanies\Profile\Controller\Cart;

	class Simple extends \ForeverCompanies\Profile\Controller\ApiController
	{
		protected $productloader;
		protected $profileHelper;
		protected $quoteHelper;
		protected $simpleHelper;
		
		public function __construct(
			\Magento\Catalog\Model\ProductFactory $productloader,
			\ForeverCompanies\Profile\Helper\Profile $profileHelper,
			\ForeverCompanies\Profile\Helper\Quote $quoteHelper,
			\ForeverCompanies\Profile\Helper\Product\Simple $simpleHelper,
			\Magento\Backend\App\Action\Context $context
		) {
			$this->productloader = $productloader;
			$this->profileHelper = $profileHelper;
			$this->quoteHelper = $quoteHelper;
			$this->simpleHelper = $simpleHelper;
			parent::__construct($context);
		}

		public function execute()
		{
			$result = [
				'success' => false
			];
			
			try{
				$post = $this->profileHelper->getPost();
				
				if ($this->profileHelper->formKeyValidator->validate($this->getRequest())) {

					$productId = $post->product;
					$options = $post->options;
					
					// get the quote id
					$quoteId = $this->quoteHelper->getQuoteId();
					
					// load the quote using quote repository
					$quote = $this->quoteHelper->getQuote($quoteId);
					
					if($productId > 0) {
						$simpleProductModel = $this->productloader->create()->load($productId);
						
						$item = $this->simpleHelper->addItem($simpleProductModel, $options);

						$quote->addItem($item);
						$quote->save();
						$itemId = $this->quoteHelper->getLastQuoteItemId($quoteId);

						$itemOptions = [
							'info_buyRequest' => json_encode([
								// read more: https://maxchadwick.xyz/blog/wtf-is-uenc
								'uenc' => '', // no url redirect on add to cart
								'product' => $productId,
								'selected_configurable_option' => '',
								'related_product' => '',
								'item' => $productId,
								'options' => $options,
								'qty' => "1"
							])
						];

						$this->simpleHelper->formatOptions($itemOptions, $options);
						
						$this->simpleHelper->setItemOptions($itemId, $productId, $itemOptions);
					}
					
					$quote->collectTotals()->save();
					
					$message = __(
						'You added %1 to your shopping cart.',
						$simpleProductModel->getName()
					);
					
					$result['success'] = true;
					$result['message'] = $message;
					
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