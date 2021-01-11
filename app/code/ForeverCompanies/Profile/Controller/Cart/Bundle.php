<?php

	namespace ForeverCompanies\Profile\Controller\Cart;

	class Bundle extends \ForeverCompanies\Profile\Controller\ApiController
	{
		protected $productloader;
		protected $profileHelper;
		protected $resultHelper;
		protected $bundleHelper;
        
		public $bundleSelectionProductIds;
        public $bundleDynamicOptionIds;
		
		public function __construct(
			\Magento\Catalog\Model\ProductFactory $productloader,
			\ForeverCompanies\Profile\Helper\Profile $profileHelper,
			\ForeverCompanies\Profile\Helper\Result $resultHelper,
			\ForeverCompanies\Profile\Helper\Product\Bundle $bundleHelper,
			\Magento\Backend\App\Action\Context $context
		) {
			$this->productloader = $productloader;
			$this->profileHelper = $profileHelper;
			$this->resultHelper = $resultHelper;
			$this->bundleHelper = $bundleHelper;
			parent::__construct($context);
		}

		public function execute()
		{
			try{
				$this->profileHelper->getPost();
				
				if ($this->profileHelper->formKeyValidator->validate($this->getRequest())) {

					$bundleId = $this->profileHelper->getPostParam('product');
					$dynamicId = $this->profileHelper->getPostParam('dynamic_bundled_item_id');
					$options = $this->profileHelper->getPostParam('options');
					
					// added to handle standard bundled options
					$bundleProductSelections = $this->profileHelper->getPostParam('bundle_option');
					$bundleCustomOptionsValues = $this->profileHelper->getPostParam('bundle_child_options');
					
					$bundleCustomOptions = array();
					
					// convert from object to array
					foreach($bundleCustomOptionsValues as $bundleCustomOption) {
						foreach($bundleCustomOption->selections as $selection) {
							foreach($selection->options as $option) {
                                if(isset($option->option_id) == true) {
                                    $bundleCustomOptions[$selection->selection_id][$option->option_id] = $option->value;
                                }
							}
						}
					}
					
					// load the quote using quote repository
					$quote = $this->profileHelper->getQuote();
					
					// get the quote id
					$quoteId = $quote->getId();
					
					if($bundleId > 0) {
						$bundleProductModel = $this->productloader->create()->load($bundleId);
						
						$validationResult = $this->resultHelper->validateBundleProductOptions($bundleProductModel, $options, $bundleProductSelections, $bundleCustomOptions, $dynamicId );
						
						if($validationResult == false) {
						
							// get the identity for the product to identify uniquely
							$this->getBundleIdentity($bundleProductModel, $bundleProductSelections);
							
							$this->bundleHelper->setBundleSelectionProductIds($this->bundleSelectionProductIds);

							// pulls all associated bundle options for item with no selection
							$bundleOptions = $this->bundleHelper->getBundleOptions($bundleProductModel);
							
							// gets the bundle options for the specific item in cart with selection
							$bundleOptionValues = $this->bundleHelper->formatBundleOptionSelection();

							if($dynamicId > 0) {
								$dynamicProductModel = $this->productloader->create()->load($dynamicId);
								
								$parentItem = $this->bundleHelper->addParentItem($bundleProductModel, $options, $dynamicProductModel);
							} else {
								$parentItem = $this->bundleHelper->addParentItem($bundleProductModel, $options);
							}
                            
                            // if the user doesn't have a quote yet create one
                            if(!$quoteId > 0) {
                                $quote->setIsActive(1);
                                $quote->save();
                                
                                $quoteId = $quote->getId();
                            }

							$quote->addItem($parentItem);
							$quote->save();
							$parentItemId = $this->profileHelper->getLastQuoteItemId($quoteId);

							$itemOptions = [
								'info_buyRequest' => json_encode([
									// read more: https://maxchadwick.xyz/blog/wtf-is-uenc
									'uenc' => '', // no url redirect on add to cart
									'product' => $bundleId,
									'selected_configurable_option' => '',
									'related_product' => '',
									'item' => $bundleId,
									'bundle_option' => $bundleOptionValues,
									'dynamic_bundled_item_id' => $dynamicId,
									'dynamic_custom_options' => ((isset($bundleCustomOptions) == true) ? $bundleCustomOptions: []),
									'options' => $options,
									'qty' => "1"
								]),
								'bundle_identity' =>  $this->bundleIdentity
							];

							$this->bundleHelper->formatBundleOptionsParent($itemOptions, $options);
							$this->bundleHelper->formatBundleOptionIds($itemOptions, $bundleOptions);
							$this->bundleHelper->formatBundleSelectionsParent($itemOptions);
							
							$this->bundleHelper->setItemOptions($parentItemId, $bundleId, $itemOptions);
							
							// iterate through native bundle options
							foreach($this->bundleSelectionProductIds as $selectionId => $bundle)
							{
								// implements the dynamic product when enabled
								if(
                                    is_array($this->bundleDynamicOptionIds) == true &&
                                    in_array($bundle['option_id'], $this->bundleDynamicOptionIds) == true
                                ) {
									$childId = $dynamicId;
								} else {
									$childId = $bundle['product_id'];
								}
									
								$childProductModel = $this->productloader->create()->load($childId);
								
								// parse out the custom options for the selection
								if(isset($bundleCustomOptions[$selectionId]) == true) {
									$childCustomOptions = $bundleCustomOptions[$selectionId];
								} else {
									$childCustomOptions = [];
								}
								
								// child item handling
								$childItem = $this->bundleHelper->addChildItem($childProductModel, $parentItemId, $childCustomOptions);
								$quote->addItem($childItem);
								$quote->save();
								$itemId = $this->profileHelper->getLastQuoteItemId($quoteId);
								
								$itemOptions = [
									'info_buyRequest' => json_encode([
										// read more: https://maxchadwick.xyz/blog/wtf-is-uenc
										'uenc' => '', // no url redirect on add to cart
										'product' => $childId,
										'selected_configurable_option' => '',
										'related_product' => '',
										'item' => $bundleId,
										'bundle_option' => $bundleOptionValues,
										// conditionally set child custom option values if they are provided
										'options' => $childCustomOptions,
										'qty' => 1
									]),
									'bundle_identity' => $this->bundleIdentity
								];
								
								$this->bundleHelper->formatBundleOptionIds($itemOptions, $bundleOptions);
								$this->bundleHelper->formatBundleSelectionsChild($itemOptions, $selectionId, $bundle);
								$this->bundleHelper->setItemOptions($itemId, $childId, $itemOptions);
							}
							
							$quote->collectTotals()->save();
							
							$this->profileHelper->saveQuote();
							
							$message = __(
								'You added %1 to your shopping cart.',
								$bundleProductModel->getName()
							);
							
							$this->resultHelper->setSuccess(true, $message);
							
						}
					} else {
						$this->resultHelper->addProductError($bundleId, "Product ID is invalid.");
					}
					
					// updates the last sync time
					$this->profileHelper->sync();
					
					$this->resultHelper->setProfile(
						$this->profileHelper->getProfile()
					);
					
				} else {
					$this->resultHelper->addFormKeyError();
				}
			
			} catch (\Exception $e) {
				$this->resultHelper->addExceptionError($e);
			}
			
			$this->resultHelper->getResult();
		}
		
		/**
		 * get the unique identifer used in cart/quote
		 * @param $product
		 * @return mixed
		 */
		public function getBundleIdentity(\Magento\Catalog\Model\Product $product = null, $bundleProductSelectionsValues)
		{
			$bundleSelectionProductIds = array();
			$bundleSelectionsId = array();
			$bundleProductSelections = array();
			
			// convert from object to array
			foreach($bundleProductSelectionsValues as $bundleProductSelection) {
				$bundleProductSelections[$bundleProductSelection->id] = $bundleProductSelection->value;
			}
			
			// get bundled options
			$optionsCollection = $product->getTypeInstance(true)
				->getOptionsCollection($product);

			foreach ($optionsCollection as $option){
				
				if($option->getIsDynamicSelection() == 1){
					$this->bundleDynamicOptionIds[] = $option->getOptionId();
				}
				
				// handle native bundle
				$selections = $product->getTypeInstance(true)
					->getSelectionsCollection($option->getOptionId(),$product);
					
				
				foreach( $selections as $selection )
				{
					if(isset($bundleProductSelections[$option->getId()]) == true && $bundleProductSelections[$option->getId()] == $selection->getSelectionId())
					{
						$bundleSelectionsId[] = $selection->getSelectionId();
                        
						// native selection mapping
						$bundleSelectionProductIds[$selection->getSelectionId()] = array(
							'product_id' => $selection->getProductId(),
                            'selection_price' => $selection->getSelectionPriceValue(),
							'price' => $selection->getPrice(),
							'option_id' => $option->getOptionId(),
							'option_title' => $option->getTitle()
						);
						
						break;
					}
				}
			}
			
			// format identifier string
			$this->bundleIdentity = $product->getId() . "_" . implode("_1_", $bundleSelectionsId) . "_1";
			
			// used by other functions to map products into cart
			$this->bundleSelectionProductIds = $bundleSelectionProductIds;
		}
	}