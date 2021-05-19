<?php

namespace ForeverCompanies\Gifts\Controller\Cart;

use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Checkout\Model\Cart as CustomerCart;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Checkout\Controller\Cart\Add as CoreAdd;

use Magento\Framework\Event\ManagerInterface as EventManager;;


class Add extends CoreAdd
{
    
    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;
    
    private $eventManager;
    
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
        ProductRepositoryInterface $productRepository,
        EventManager $eventM
        ) {
            parent::__construct(
                $context,
                $scopeConfig,
                $checkoutSession,
                $storeManager,
                $formKeyValidator,
                $cart,
                $productRepository
                );
            $this->productRepository = $productRepository;
            $this->eventManager = $eventM;
    }
    
    
    public function execute()
    {
        
        if (!$this->_formKeyValidator->validate($this->getRequest())) {
            $this->messageManager->addErrorMessage(
                __('Your session has expired')
                );  
            return $this->resultRedirectFactory->create()->setPath('*/*/');
        }
        
        $params = $this->getRequest()->getParams();
        try {
            if (isset($params['qty'])) {
                $filter = new \Zend_Filter_LocalizedToNormalized(
                    ['locale' => $this->_objectManager->get(
                        \Magento\Framework\Locale\ResolverInterface::class
                        )->getLocale()]
                    );
                $params['qty'] = $filter->filter($params['qty']);
            }
            
            $product = $this->_initProduct();
            $related = $this->getRequest()->getParam('related_product');
            
            /** Check product availability */
            if (!$product) {
                return $this->goBack();
            }
            
            $this->cart->addProduct($product, $params);
            if (!empty($related)) {
                $this->cart->addProductsByIds(explode(',', $related));
            }
            $this->cart->save();
            
            
            /**
             * @todo remove wishlist observer \Magento\Wishlist\Observer\AddToCart
             */
            $this->_eventManager->dispatch(
                'checkout_cart_add_product_complete',
                ['product' => $product, 'request' => $this->getRequest(), 'response' => $this->getResponse()]
                );
            
            $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/giftlog.log');
            $logger = new \Zend\Log\Logger();
            $logger->addWriter($writer);
            $logger->info("bbb");
            
            $this->eventManager->dispatch('free_gift_add_logic');
            
            if (!$this->_checkoutSession->getNoCartRedirect(true)) {
                if ($this->shouldRedirectToCart()) {
                    $message = __(
                        'You added %1 to your shopping cart.',
                        $product->getName()
                        );
                    $this->messageManager->addSuccessMessage($message);
                } else {
                    $this->messageManager->addComplexSuccessMessage(
                        'addCartSuccessMessage',
                        [
                            'product_name' => $product->getName(),
                            'cart_url' => $this->getCartUrl(),
                        ]
                        );
                }
                if ($this->cart->getQuote()->getHasError()) {
                    $errors = $this->cart->getQuote()->getErrors();
                    foreach ($errors as $error) {
                        $this->messageManager->addErrorMessage($error->getText());
                    }
                }
                
                return $this->goBack(null, $product);
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            if ($this->_checkoutSession->getUseNotice(true)) {
                $this->messageManager->addNoticeMessage(
                    $this->_objectManager->get(\Magento\Framework\Escaper::class)->escapeHtml($e->getMessage())
                    );
            } else {
                $messages = array_unique(explode("\n", $e->getMessage()));
                foreach ($messages as $message) {
                    $this->messageManager->addErrorMessage(
                        $this->_objectManager->get(\Magento\Framework\Escaper::class)->escapeHtml($message)
                        );
                }
            }
            
            $url = $this->_checkoutSession->getRedirectUrl(true);
            if (!$url) {
                $url = $this->_redirect->getRedirectUrl($this->getCartUrl());
            }
            
            return $this->goBack($url);
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage(
                $e,
                __('We can\'t add this item to your shopping cart right now.')
                );
            $this->_objectManager->get(\Psr\Log\LoggerInterface::class)->critical($e);
            return $this->goBack();
        }
        
        return $this->getResponse();
    }
    
    private function shouldRedirectToCart()
    {
        return $this->_scopeConfig->isSetFlag(
            'checkout/cart/redirect_to_cart',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
    
}
