<?php

	namespace ForeverCompanies\DynamicBundle\Controller\Add;

	use Magento\Framework\App\Action\Context;
	use Magento\Checkout\Model\Cart;

	class Index extends \Magento\Framework\App\Action\Action
	{
		protected $cart;
		protected $quoteRepository;
		protected $quoteManagement;
		protected $guestCart;
		protected $productRepository;
		protected $cartItemFactory;
		protected $productloader;
		protected $optioncollection;
		protected $itemoption;

		public function __construct(
			Context $context,
			Cart $cart,
			\Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
			\Magento\Quote\Api\CartManagementInterface $quoteManagement,
			\Magento\Quote\Api\GuestCartManagementInterface $guestCart,
			\Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
			\Magento\Quote\Api\Data\CartItemInterfaceFactory $cartItemFactory,
			\Magento\Catalog\Model\ProductFactory $productloader,
			\Magento\Quote\Model\ResourceModel\Quote\Item\Option\CollectionFactory $optioncollection,
			\Magento\Quote\Model\Quote\Item\Option $itemoption
		) {
			parent::__construct($context);
			$this->cart = $cart;
			$this->quoteRepository = $quoteRepository;
			$this->quoteManagement = $quoteManagement;
			$this->guestCart = $guestCart;
			$this->productRepository = $productRepository;
			$this->cartItemFactory = $cartItemFactory;
			$this->productloader = $productloader;
			$this->optioncollection = $optioncollection;
			$this->itemoption = $itemoption;
		}

		
		/**
		* add cart item
		*/
		public function execute()
		{
			ini_set("display_errors", 1);
			
			try{
				$post = $this->getRequest()->getParams();

				//$bundleId = $post['product'];
				//$childId = $post['dynamic_bundled_item_id'];
				//$options = $post['options'];
				
				$bundleId = 8;
				$childId = 7;
				$options = array(
					5 => 27,
					6 => 30
				);

				// clear out old cart contents
				 $this->cart->truncate();
				
				// get the quote id
				$quoteId = $this->cart->getQuote()->getId();
				
				if(!$quoteId > 0) {
					// save the quote to create an instance
					$this->cart->saveQuote();
					
					// fetch the card id again
					$quoteId = $this->cart->getQuote()->getId();
				} else {
					
				}
				
				// load the quote factory
				$quote = $this->quoteRepository->get($quoteId);
				
				if($bundleId > 0) {
					$parentItem = $this->addParentItem($bundleId);
					$quote->addItem($parentItem);
					$quote->save();
					
					$itemId = $this->getLastItemId($quoteId);
					
					// we will use this to associate the loose stone product
					$parentId = $itemId;
					
					//$buyRequest = {[;
					
					$itemOptions = array(
						'info_buyRequest' => '{"uenc":"aHR0cHM6Ly9wYXVsdHdvLjEyMTVkaWFtb25kcy5jb20vdGVzdC1hd2Vzb21lLXJpbmcuaHRtbA,,","product":"8","selected_configurable_option":"","related_product":"","item":"8","bundle_option":{"2":"6"},"dynamic_bundled_item_id":"6","options":{"5":"28","6":"32"},"qty":"1"}',
						'bundle_identity' => "{$bundleId}_{$childId}_1",
						'bundle_option_ids' => '[2]',
						'bundle_selection_ids' => '["6"]'
					);

					//$this->formatBuyRequest($options, $childId);
					$this->formatBundleOptionsParent($itemOptions, $options);
					$this->formatBundleSelectionsParent($itemOptions, $options);
					
					$this->setItemOptions($itemId, $bundleId, $itemOptions);
				}
				
				if($childId > 0 && $parentId) {
					$childItem = $this->addChildItem($childId, $parentId);
					$quote->addItem($childItem);
					$quote->save();
					
					$itemId = $this->getLastItemId($quoteId);
					
					$itemOptions = array(
						'selection_qty_6' => '1',
						'product_qty_7' => '1',
						'selection_id' => '6',
						'info_buyRequest' => '{"uenc":"aHR0cHM6Ly9wYXVsdHdvLjEyMTVkaWFtb25kcy5jb20vdGVzdC1hd2Vzb21lLXJpbmcuaHRtbA,,","product":"8","selected_configurable_option":"","related_product":"","item":"8","bundle_option":{"2":"6"},"dynamic_bundled_item_id":"6","options":{"5":"28","6":"32"},"qty":"1"}',
						'bundle_option_ids' => '[2]',
						'bundle_selection_attributes' => '{"price":372,"qty":1,"option_label":"Center Stone","option_id":"2"}',
						'bundle_identity' => "{$bundleId}_{$childId}_1",
					);
					
					$this->setItemOptions($itemId, $childId, $itemOptions);
				}
				
				$quote->collectTotals()->save();
				
				$product = $this->productloader->create()->load($bundleId);
				
				$message = __(
					'You added %1 to your shopping cart.',
					$product->getName()
				);
				
				$this->messageManager->addSuccessMessage($message);
				
				//return $this->resultRedirectFactory->create()->setUrl($this->_url->getUrl('checkout/cart'));
				
			} catch (\Exception $e) {
				echo $e->getMessage();
			}
		}

		/**
		* get last cart item added
		*/
		private function getLastItemId($quoteId = 0)
		{
			$_objectManager = \Magento\Framework\App\ObjectManager::getInstance();
			$collecion = $_objectManager->create('Magento\Quote\Model\ResourceModel\Quote\Item\Collection')->addFieldToFilter('quote_id',$quoteId);
			
			$lastitem = $collecion->getLastItem();
			
			return $lastitem->getId();
		}
		
		private function formatBuyRequest()
		{
			
		}
		
		/**
		* format bundle custom options
		*/
		private function formatBundleOptionsParent(&$itemOptions = null, $options = null)
		{
			$itemOptions['option_ids'] = implode(',',array_keys($options));
			
			foreach($options as $key => $value)
			{
				$itemOptions['option_' . $key] = $value;
			}
		}
		
		private function formatBundleSelectionsParent(&$itemSelections = null, $options = null)
		{
			foreach($options as $key => $value)
			{
				$itemOptions[$key] = $value;
			}
		}
		
		private function formatBundleSelectionsChild(&$itemSelections = null, $selections = null)
		{
			
		}
		
		private function setItemOptions($itemId = 0, $productId = 0, $options = null)
		{
			$itemoption = $this->itemoption;
			
			foreach($options as $key => $value)
			{
					$itemoption->unsetData();
					$itemoption->setItemId($itemId);
					$itemoption->setProductId($productId);
                    $itemoption->setCode($key);
					$itemoption->setValue($value);
                    $itemoption->save();
			}
		}
		
		private function addParentItem($productId = 0)
		{
			try{
				$product = $this->productloader->create()->load($productId);
				
				if ($product->getId()) {
					$quoteItem = $this->cartItemFactory->create();
					$quoteItem->setProduct($product);
					
					// set the values specific to what they need to be...
					$quoteItem->setProductType('bundle');
					$quoteItem->setSku('1235');
					$quoteItem->setName('Test Bundle Item');
					$quoteItem->setQty(1);
					$quoteItem->setCustomPrice(100);
					$quoteItem->setOriginalCustomPrice(200);
					$quoteItem->setRowTotal(100);
					$quoteItem->setBaseRowTotal(100);
					$quoteItem->getProduct()->setIsSuperMode(true);
					
					return $quoteItem;
				}
			} catch (\Exception $e) {
				echo $e->getMessage();
			}
		}
		
		private function addChildItem($productId = 0, $parentId = 0)
		{
			try{
				$product = $this->productloader->create()->load($productId);
				
				if ($product->getId()) {
					$quoteItem = $this->cartItemFactory->create();
					$quoteItem->setProduct($product);
					
					// set the values specific to what they need to be...
					$quoteItem->setParentItemId($parentId);
					$quoteItem->setSku('56456456');
					$quoteItem->setName('Test Diamond Item');
					$quoteItem->setQty(1);
					$quoteItem->setCustomPrice(100);
					$quoteItem->setOriginalCustomPrice(200);
					$quoteItem->setRowTotal(100);
					$quoteItem->setBaseRowTotal(100);
					
					return $quoteItem;
				}
			} catch (\Exception $e) {
				echo $e->getMessage();
			}
		}
		
		/**
		 * get all the selection products used in bundle product
		 * @param $product
		 * @return mixed
		 */
		private function getBundleOptions(Product $product)
		{
			$selectionCollection = $product->getTypeInstance()
				->getSelectionsCollection(
					$product->getTypeInstance()->getOptionsIds($product),
					$product
				);
			$bundleOptions = [];
			foreach ($selectionCollection as $selection) {
				$bundleOptions[$selection->getOptionId()][] = $selection->getSelectionId();
			}
			return $bundleOptions;
		}
	}