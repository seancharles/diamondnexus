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
	protected $cart;
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
		\Magento\Framework\App\RequestInterface $request,
		\Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator,
		\Magento\Customer\Model\Session $customerSession,
		\Magento\Checkout\Model\Session $checkoutSession,
		\Magento\Quote\Api\CartRepositoryInterface $cartRepository,
        \Magento\Quote\Model\ResourceModel\Quote\Item\CollectionFactory $quoteItemCollectionFactory,
		\Magento\Framework\Data\Form\FormKey $formKey,
		\Magento\Backend\App\Action\Context $context
	) {
		$this->request = $request;
		$this->formKeyValidator = $formKeyValidator;
		$this->customerSession = $customerSession;
		$this->checkoutSession = $checkoutSession;
		$this->cartRepository = $cartRepository;
        $this->quoteItemCollectionFactory = $quoteItemCollectionFactory;
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
		if($this->checkoutSession->getQuote()->getId() > 0) {
			$this->setProfileKey('quote_id', (int) $this->checkoutSession->getQuote()->getId());
			$this->setProfileKey('cart_items', $this->getCartItems());
			$this->setProfileKey('cart_qty', (int) $this->getCartQty());
		} else {
			//$this->setProfileKey('cart_items', null);
			$this->setProfileKey('cart_qty', 0);
		}
	}
	
	public function getCartItems()
	{
		$items = [];
		
		foreach($this->checkoutSession->getQuote()->getItems() as $item) {
			$items[] = [
				'item_id' => $item->getId(),
				'name' => $item->getName(),
				'sku' => $item->getSku(),
				'price' => $item->getPrice()
			];
		}
		
		return $items;
	}
	
	public function getCartQty()
	{
		$cartQty = 0;
		$items = $this->checkoutSession->getQuote()->getItems();
		
		if(isset($items) == true) {
			foreach ($items as $item){
				$cartQty += $item->getQty();
			}
		}
		
		return $cartQty;
	}
	
	public function getPost($postType = self::POST_TYPE_OBJECT, $postArray = false)
	{
        $this->postType = $postType;
        
        if($this->postType == self::POST_TYPE_OBJECT) {
        
            // parse the json post
            $json = file_get_contents('php://input');

            // Converts it into a PHP object
            $data = json_decode($json);

            if(isset($data->form_key) == true) {
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
        if($this->postType == self::POST_TYPE_OBJECT) {
            if(isset($this->post->{$field}) == true) {
                return $this->post->{$field};
            }
        } else {
            if(isset($this->post[$field]) == true) {
                return $this->post[$field];
            }
        }

		return null;
	}
	
	public function sync()
	{
		$now = time();
		
		$this->customerSession->setLastSync($now);
		
		$this->setProfileKey('lastsync', $now);
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
	
	public function clearQuote()
	{
		$quoteItems = $this->checkoutSession->getQuote()->getAllVisibleItems();
		
		foreach ($quoteItems as $item) {
			$item->delete();
		}
		
		$this->saveQuote();
	}
	
	public function getQuote()
	{
		// load the quote using quote repository
		return $this->checkoutSession->getQuote();
	}
	
	public function saveQuote()
	{
		$this->cartRepository->save($this->getQuote());
		
		// workaround to load the quote after it was updated to fetch qty and items
		// otherwise this information might be off from the actual cart data, this
		// might not be the best way to implement
		$quote = $this->cartRepository->get($this->checkoutSession->getQuote()->getId());

		$cartQty = 0;
		$items = [];
		
		foreach($quote->getItems() as $item) {
			$items[] = [
				'item_id' => $item->getId(),
				'name' => $item->getName(),
				'sku' => $item->getSku(),
				'price' => $item->getPrice()
			];
			
			$cartQty += $item->getQty();
		}
		
		// updating profile info to match quote after the quote is saved (don't do this before)
		$this->setProfileKey("cart_qty", $cartQty);
		$this->setProfileKey("cart_items", $items);
	}
	
	/**
	* get last cart item added
	*/
	public function getLastQuoteItemId($quoteId = 0)
	{
        $collection = $this->quoteItemCollectionFactory->create();
		$collection->addFieldToFilter('quote_id',$quoteId);
		
		return $collection->getLastItem()->getId();
	}
}