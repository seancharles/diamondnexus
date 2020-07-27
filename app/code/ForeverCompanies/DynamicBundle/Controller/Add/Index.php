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

		public function __construct(
			Context $context,
			Cart $cart,
			\Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
			\Magento\Quote\Api\CartManagementInterface $quoteManagement,
			\Magento\Quote\Api\GuestCartManagementInterface $guestCart,
			\Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
			\Magento\Quote\Api\Data\CartItemInterfaceFactory $cartItemFactory,
			\Magento\Catalog\Model\ProductFactory $productloader
		) {
			parent::__construct($context);
			$this->cart = $cart;
			$this->quoteRepository = $quoteRepository;
			$this->quoteManagement = $quoteManagement;
			$this->guestCart = $guestCart;
			$this->productRepository = $productRepository;
			$this->cartItemFactory = $cartItemFactory;
			$this->productloader = $productloader;
		}

		
		/**
		* add cart item
		*/
		public function execute()
		{
			ini_set("display_errors", 1);
			
			// clear out old cart contents
			 //$this->cart->truncate();

			/*
			$product = $this->productFactory->create()->load($productId);
			$productsArray = $this->getBundleOptions($product);
			$params = [
				'product' => $productId,
				'bundle_option' => $productsArray,
				'qty' => $qty,
				'options' => array(
					5 => 27,
					6 => 30
				),
				'dynamic_bundled_item_id' => 6
			];
			*/

			try{

				$bundleId = $this->getRequest()->getParam('parent_id');
				$childId = $this->getRequest()->getParam('child_id');
				
				$post = $this->getRequest()->getParams();
				
				// get the quote id
				$quoteId = $this->cart->getQuote()->getId();
				
				if(!$quoteId > 0) {
					// save the quote to create an instance
					$this->cart->saveQuote();
					
					// fetch the card id again
					$quoteId = $this->cart->getQuote()->getId();
				}
				
				// load the quote factory
				$quote = $this->quoteRepository->get($quoteId);
				
				if($bundleId > 0) {
					$parentItem = $this->addParentItem($bundleId);
					$quote->addItem($parentItem);
					$quote->save();
					
					$_objectManager = \Magento\Framework\App\ObjectManager::getInstance();
					$collecion = $_objectManager->create('Magento\Quote\Model\ResourceModel\Quote\Item\Collection')->addFieldToFilter('quote_id',$quoteId);
					
					$lastitem = $collecion->getLastItem();
					$parentId = $lastitem->getId();
				}
				
				if($childId > 0 && $parentId) {
					$childItem = $this->addChildItem($childId, $parentId);
					$result = $quote->addItem($childItem);
				}
				
				$quote->collectTotals()->save();
				
				echo "Add complete";
				
			} catch (\Exception $e) {
				echo $e->getMessage();
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
					
					/*
					$quoteItem->setAdditionalData(serialize(array(
						'product' => $productId,
						'code'  => 'test',
						'value' => '1'
					)));
					*/
					
					# {"product":"8","bundle_option":{"2":"13"},"dynamic_bundled_item_id":"6","options":{"5":"27","6":"30"},"qty":"1"}
					
					/*
					$quoteItem->setOptions([
						'product' => $productId,
						'code'  => 'test',
						'value' => '1'
					]);
					*/
					
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
					//$quoteItem->getProduct()->setIsSuperMode(true);
					
					return $quoteItem;
				}
			} catch (\Exception $e) {
				echo $e->getMessage();
			}
		}
		
		private function setItemOptions($quoteItem, $productId = 0, $options = null)
		{
			$quoteItem->addOption([
				'product' => $productId,
				'code'  => 'test',
				'value' => '1'
			]);
			
			//$quoteItem->saveItemOptions();
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