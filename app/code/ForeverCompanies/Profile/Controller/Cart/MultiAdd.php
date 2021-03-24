<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace ForeverCompanies\Profile\Controller\Cart;

use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magento\Checkout\Model\Cart as CustomerCart;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Controller for processing add to cart action.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class MultiAdd extends \Magento\Checkout\Controller\Cart implements HttpPostActionInterface
{
    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator
     * @param CustomerCart $cart
     * @param ProductRepositoryInterface $productRepository
     * @codeCoverageIgnore
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator,
        CustomerCart $cart,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Quote\Model\ResourceModel\Quote\Item\CollectionFactory $quoteItemCollectionFactory
    ) {
        parent::__construct(
            $context,
            $scopeConfig,
            $checkoutSession,
            $storeManager,
            $formKeyValidator,
            $cart
        );
        $this->productRepository = $productRepository;
        $this->quoteItemCollectionFactory = $quoteItemCollectionFactory;
    }
    
     /**
     * Add product to shopping cart action
     *
     * @return ResponseInterface|ResultInterface
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute()
    {
        $params = $this->getRequest()->getParams();

        $settingId = (int) $params['setting']['id'];
        $stoneId = (int) $params['stone']['id'];

        $settingParams = ['product' => $settingId];
        $settingParams['super_attribute'][145] = $params['setting']['super_attribute'][145];
        $settingParams['super_attribute'][149] = $params['setting']['super_attribute'][149];
        $settingParams['options'][6099] = $params['setting']['options'][6099];
        
        $stoneParams = [
            'product' => $stoneId,
            'parent_id' => 123
        ];
        
        $setId = time();
        
        $this->_checkoutSession->setParentItemId(null);
        $this->_checkoutSession->setBundleIdentifier($setId);
        
        $this->addItem($settingId, $settingParams);
        $parentItemId = $this->getLastItemId($setId);
        
        $this->_checkoutSession->setParentItemId($parentItemId);
        $this->addItem($stoneId, $stoneParams);

        echo "Success!";
    }
    
    protected function addItem($productId, $params)
    {
        $storeId = $this->_objectManager->get(
            \Magento\Store\Model\StoreManagerInterface::class
        )->getStore()->getId();
        
        $product = $this->productRepository->getById($productId, false, $storeId);
        
        $this->cart->addProduct($product, $params);
        $this->cart->save();

        $this->_eventManager->dispatch(
            'checkout_cart_add_product_complete',
            ['product' => $product, 'request' => $this->getRequest(), 'response' => $this->getResponse()]
        );
    }
    
    protected function getLastItemId($setId = null)
    {
        $quoteId = $this->cart->getQuote()->getId();
        
        $collection = $this->quoteItemCollectionFactory->create();
		$collection->addFieldToFilter('quote_id', $quoteId);
        $collection->addFieldToFilter('set_id', $setId);
        
        if($collection) {
            $firstItem = $collection->getFirstItem();
            
            return $firstItem->getItemId();
        }
        
        return null;
    }
}
