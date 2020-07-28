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

				
				$bundleId = $post['product'];
				$childId = $post['dynamic_bundled_item_id'];
				$options = $post['options'];
				
				/*
				$bundleId = 8;
				$childId = 7;
				$options = array(
					5 => 27,
					6 => 30
				);
				*/

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
					$bundleProductModel = $this->productloader->create()->load($bundleId);
					$bundleOptions = $this->getBundleOptions($bundleProductModel);
					
					$parentItem = $this->addParentItem($bundleProductModel);
					$quote->addItem($parentItem);
					$quote->save();
					$parentItemId = $this->getLastQuoteItemId($quoteId);
					
					$itemOptions = array(
						'info_buyRequest' => '{"uenc":"aHR0cHM6Ly9wYXVsdHdvLjEyMTVkaWFtb25kcy5jb20vdGVzdC1hd2Vzb21lLXJpbmcuaHRtbA,,","product":"8","selected_configurable_option":"","related_product":"","item":"8","bundle_option":{"2":"6"},"dynamic_bundled_item_id":"6","options":{"5":"28","6":"32"},"qty":"1"}',
						'bundle_identity' => "{$bundleId}_{$childId}_1"
					);

					$this->formatBundleOptionsParent($itemOptions, $options);
					$this->formatBundleSelectionsParent($itemOptions, $bundleOptions);
					$this->setItemOptions($parentItemId, $bundleId, $itemOptions);
				}
				
				if($childId > 0 && $parentItemId) {
					$childProductModel = $this->productloader->create()->load($childId);
					$childItem = $this->addChildItem($childProductModel, $parentItemId);
					$quote->addItem($childItem);
					$quote->save();
					$itemId = $this->getLastQuoteItemId($quoteId);
					
					$itemOptions = array(
						'product_qty_' . $childId => '1',
						'info_buyRequest' => '{"uenc":"aHR0cHM6Ly9wYXVsdHdvLjEyMTVkaWFtb25kcy5jb20vdGVzdC1hd2Vzb21lLXJpbmcuaHRtbA,,","product":"8","selected_configurable_option":"","related_product":"","item":"8","bundle_option":{"2":"6"},"dynamic_bundled_item_id":"6","options":{"5":"28","6":"32"},"qty":"1"}',
						'bundle_selection_attributes' => '{"price":372,"qty":1,"option_label":"Center Stone","option_id":"2"}',
						'bundle_identity' => "{$bundleId}_{$childId}_1",
					);
					
					$this->formatBundleSelectionsChild($itemOptions, $bundleOptions);
					$this->setItemOptions($itemId, $childId, $itemOptions);
				}
				
				$quote->collectTotals()->save();
				
				$message = __(
					'You added %1 to your shopping cart.',
					$bundleProductModel->getName()
				);
				
				$this->messageManager->addSuccessMessage($message);
				
				return $this->resultRedirectFactory->create()->setUrl($this->_url->getUrl('checkout/cart'));
				
			} catch (\Exception $e) {
				echo $e->getMessage();
			}
		}

		/**
		* get last cart item added
		*/
		private function getLastQuoteItemId($quoteId = 0)
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
		
		private function formatBundleSelectionsParent(&$itemSelections = null, $selections = null)
		{
			foreach($selections as $key => $value)
			{
				$itemSelections['bundle_option_ids'] = '[' . $key . ']';
				
				$itemSelections['bundle_selection_ids'] = '["' . implode(",", $value) . '"]';
			}
		}
		
		private function formatBundleSelectionsChild(&$itemSelections = null, $selections = null)
		{
			foreach($selections as $key => $value)
			{
				$itemSelections['bundle_option_ids'] = '[' . $key . ']';
				
				$itemSelections['selection_id'] = $value[0];
				$itemSelections['selection_qty_' . $value[0]] = 1;
			}
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
		
		private function addParentItem($bundleProductModel)
		{
			try{
				if ($bundleProductModel->getId()) {
					$quoteItem = $this->cartItemFactory->create();
					$quoteItem->setProduct($bundleProductModel);
					
					// set the values specific to what they need to be...
					$quoteItem->setProductType('bundle');
					//$quoteItem->setSku('1235');
					$quoteItem->setName('Test Bundle Item');
					$quoteItem->setQty(1);
					//$quoteItem->setCustomPrice(100);
					//$quoteItem->setOriginalCustomPrice(200);
					//$quoteItem->setRowTotal(100);
					//$quoteItem->setBaseRowTotal(100);
					//$quoteItem->getProduct()->setIsSuperMode(true);
					
					return $quoteItem;
				}
			} catch (\Exception $e) {
				echo $e->getMessage();
			}
		}
		
		private function addChildItem($childProductModel, $parentId = 0)
		{
			try{
				if ($childProductModel->getId()) {
					$quoteItem = $this->cartItemFactory->create();
					$quoteItem->setProduct($childProductModel);
					
					$price = $childProductModel->getPrice();
					
					// set the values specific to what they need to be...
					$quoteItem->setParentItemId($parentId);
					$quoteItem->setSku($childProductModel->getSku());
					$quoteItem->setName($childProductModel->getName());
					$quoteItem->setQty(1);
					$quoteItem->setCustomPrice($price);
					$quoteItem->setOriginalCustomPrice($price);
					$quoteItem->setRowTotal($price);
					$quoteItem->setBaseRowTotal($price);
					
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
		private function getBundleOptions($product)
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