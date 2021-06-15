<?php

namespace ForeverCompanies\Checkout\Controller\Createaccount;

class Index extends \ForeverCompanies\Forms\Controller\ApiController
{
	protected $formKeyValidator;
	protected $storeManager;
    protected $cookieManager;
	protected $submissionFactory;
	protected $formHelper;
    
    const COOKIE_NAME = 'submission_key';
    const COOKIE_DURATION = 86400; // lifetime in seconds

	public function __construct(
		\Magento\Framework\App\Action\Context $context,
		\Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator,
		\Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager,
        \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory $cookieMetadataFactory,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Customer\Model\AddressFactory $addressFactory
	) {
		parent::__construct($context);
		
		$this->formKeyValidator = $formKeyValidator;
		$this->storeManager = $storeManager;
        $this->cookieManager = $cookieManager;
        $this->cookieMetadataFactory = $cookieMetadataFactory;
        $this->checkoutSession = $checkoutSession;
        $this->customerFactory = $customerFactory;
        $this->orderFactory = $orderFactory;
        $this->addressFactory = $addressFactory;
	}

    /**
     * @return mixed
     */
    public function execute()
    {
        $result = [
            'success' => false,
            'message' => 'Your account has been created.'
        ];
        
        $websiteId = $this->storeManager->getStore()->getWebsiteId();
        $guestOrderId = $this->checkoutSession->getGuestOrderId();
        $post = $this->getRequest()->getPost();
        $password = $post['password'];
        
        // check for valid form key
        if ($this->formKeyValidator->validate($this->getRequest()) == false) {
            $result['message'] = 'Invalid form key';
        } else {
            // check the session for guest order id set via observer
            if($guestOrderId > 0) {
                $order = $this->orderFactory->get($guestOrderId);
                
                $customer = $customerFactory->setWebsiteId($websiteId)->loadByEmail($order->getEmail());
                
                if ($customer->getId()) {
                    $result['message'] = 'Account already exists.';
                } else {
                    if($password != null) {
                        try {
                            $customer = $this->customerFactory->create();
                            $customer->setWebsiteId($websiteId);
                            $customer->setEmail($order->getEmail());
                            $customer->setFirstname($order->getFirstName());
                            $customer->setLastname($order->getLastName());
                            $customer->setPassword($password);
                            $customer->save();

                            $customer->setConfirmation(null);
                            $customer->save();

                            // save the billing address
                            $customAddress = $this->addressFactory->create();
                            $customAddress->setData($order->getBillingAddress())
                                          ->setCustomerId($customer->getId())
                                          ->setIsDefaultBilling('1')
                                          ->setIsDefaultShipping('1')
                                          ->setSaveInAddressBook('1');
                            $customAddress->save();
                            
                            // save shipping address
                            $customAddress = $this->addressFactory->create();
                            $customAddress->setData($order->getShippingAddress())
                                          ->setCustomerId($customer->getId())
                                          ->setIsDefaultShipping('1')
                                          ->setSaveInAddressBook('1');
                            $customAddress->save();
                    
                            // clear out the guest order id
                            $this->checkoutSession->setGuestOrderId('');
                    
                            $result['success'] = true;
                            
                        } catch (Exception $e) {
                            $result['success'] = false;
                            $result['message'] = $e->getMessage();
                        }
                    } else {
                        $result['message'] = "A valid password must be provided.";
                    }
                }
            } else {
                $result['message'] = 'Account could not be created, no guest order found.';
            }
        }
        
        echo(json_encode($result));
    }
}