<?php

namespace ForeverCompanies\Profile\Helper;
 
class Profile
{
    const POST_TYPE_OBJECT = 'json';
    const POST_TYPE_ARRAY = 'array';
    
    public $request;
    public $formKeyValidator;

    protected $customerSession;
    protected $checkoutSession;
    protected $cartRepository;
    protected $quoteItemCollectionFactory;
    protected $formKey;
    public $cart;
    protected $post;
    protected $postType;
    
    protected $profile = [
        'form_key' => null,
        'customer_id' => 0,
        'logged_in' => false,
        'lastsync' => null,
        'cart_items' => null,
        'set_builder' => [
            'type' => null,
            'setting' => null,
            'setting_sku' => null,
            'stone' => null,
            'stone_sku' => null
        ],
        'cart_qty' => 0
    ];
    
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Checkout\Model\Cart $cart,
        \Magento\Framework\Data\Form\FormKey $formKey,
        \Magento\Backend\App\Action\Context $context
    ) {
        $this->storeManager = $storeManager;
        $this->request = $request;
        $this->formKeyValidator = $formKeyValidator;
        $this->customerSession = $customerSession;
        $this->checkoutSession = $checkoutSession;
        $this->productRepository = $productRepository;
        $this->cart = $cart;
        $this->formKey = $formKey;
        
        $this->setProfileKey('form_key', $this->formKey->getFormKey());
        $this->setProfileKey('customer_id', (int) $customerSession->getCustomerId());
        $this->setProfileKey('logged_in', (bool) $customerSession->isLoggedIn());
        
        $this->setProfileKey('set_builder', [
            'type' => $this->getProfileSessionKey('set_type'),
            'setting' => $this->getProfileSessionKey('set_setting'),
            'setting_sku' => $this->getProfileSessionKey('set_setting_sku'),
            'stone' => $this->getProfileSessionKey('set_stone'),
            'stone_sku' => $this->getProfileSessionKey('set_stone_sku')
        ]);
        
        // add cart into to
        if ($this->cart->getQuote()->getId() > 0) {
            $this->setProfileKey('quote_id', (int) $this->cart->getQuote()->getId());
            $this->setProfileKey('cart_items', $this->getCartItems());
            $this->setProfileKey('cart_qty', (int) $this->getCartQty());
        } else {
            //$this->setProfileKey('cart_items', null);
            $this->setProfileKey('cart_qty', 123);
        }
    }
    
    public function addCartItem($productId, $params, $setId = false, $save = false)
    {
        // Specific for TF ring builder
        if ($setId != false) {
            $this->checkoutSession->setBundleIdentifier($setId);
        }
        
        $storeId = $this->storeManager->getStore()->getId();
        
        $product = $this->productRepository->getById($productId, false, $storeId);

        $this->cart->addProduct($product, $params);

        if ($save !== false) {
            $this->cart->save();
        }
        
        if ($setId != false) {
            $this->checkoutSession->setBundleIdentifier(null);
        }
    }
    
    public function getCartItems()
    {
        $items = [];
        
        //print_r(get_class_methods($this->cart->getQuote()->getAllVisibleItems()));
        //exit;
        
        foreach ($this->cart->getQuote()->getAllVisibleItems() as $item) {
            $items[] = [
                'item_id' => $item->getId(),
                'set_id' => $item->getSetId(),
                'name' => $item->getName(),
                'sku' => $item->getSku(),
                'price' => $item->getPrice()
            ];
        }
        
        return $items;
    }
    
    public function getCartQty()
    {
        $sets = [];
        $cartQty = 0;

        $items = $this->cart->getQuote()->getAllVisibleItems();

        if (isset($items) == true) {
            foreach ($items as $item) {
                if ($item->getSetId() > 0) {
                    if (isset($sets[$item->getSetId()]) == false) {
                        // only count the first item
                        $cartQty += $item->getQty();
                        
                        // add set to the list
                        $sets[$item->getSetId()] = 1;
                    }
                } else {
                    $cartQty += $item->getQty();
                }
            }
        }
        
        return $cartQty;
    }
    
    public function getPost($postType = self::POST_TYPE_OBJECT, $postArray = false)
    {
        $this->postType = $postType;
        
        if ($this->postType == self::POST_TYPE_OBJECT) {
        
            // parse the json post
            $json = file_get_contents('php://input');

            // Converts it into a PHP object
            $data = json_decode($json);

            if (isset($data->form_key) == true) {
                // get form key
                $formKey = $data->form_key;
                
                // translate ajax post object to form value to validate
                $this->request->setPostValue('form_key', $formKey);
            }
        } else {
            $data = $postArray;
        }

        $this->post = $data;
    }
    
    public function getPostParam($field = null)
    {
        if ($this->postType == self::POST_TYPE_OBJECT) {
            if (isset($this->post->{$field}) == true) {
                return $this->post->{$field};
            }
        } else {
            if (isset($this->post[$field]) == true) {
                return $this->post[$field];
            }
        }

        return null;
    }
    
    public function sync()
    {
        $now = time();
        
        //$this->customerSession->setLastSync($now);
        
        $this->setProfileKey('lastsync', $now);
        
        $this->setProfileKey('cart_items', $this->getCartItems());
        $this->setProfileKey('cart_qty', $this->getCartQty());
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
    
    public function setProfileBuilderKey($key = null, $value = null)
    {
        $this->profile['set_builder'][$key] = $value;
    }

    public function getProfileSessionKey($key = null)
    {
        return $this->checkoutSession->getData($key);
    }
    
    public function setProfileSessionKey($key = null, $value = null)
    {
        $this->checkoutSession->setData($key, $value);
    }
    
    public function clearCart()
    {
        $quoteItems = $this->cart->getItems();
        
        foreach ($quoteItems as $item) {
            $item->delete();
        }
    }
    
    public function getCart()
    {
        // load the quote using quote repository
        return $this->cart;
    }
}
