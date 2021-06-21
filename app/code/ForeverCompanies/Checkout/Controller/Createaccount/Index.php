<?php

namespace ForeverCompanies\Checkout\Controller\Createaccount;

use Magento\Framework\Exception\NoSuchEntityException;

class Index extends \ForeverCompanies\Forms\Controller\ApiController
{
	protected $formKeyValidator;
	protected $storeManager;
    protected $cookieManager;
    protected $cookieMetadataFactory;
    protected $checkoutSession;
	protected $customerFactory;
    protected $customerRepository;
    protected $encryptor;
    protected $orderRepository;
	protected $formHelper;
    protected $addressRepository;
    protected $addressFactory;
    
    const COOKIE_NAME = 'submission_key';
    const COOKIE_DURATION = 86400; // lifetime in seconds

	public function __construct(
		\Magento\Framework\App\Action\Context $context,
		\Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator,
		\Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager,
        \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory $cookieMetadataFactory,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Customer\Api\Data\CustomerInterfaceFactory $customerFactory,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Framework\Encryption\Encryptor $encryptor,
        \Magento\Sales\Model\OrderRepository $orderRepository,
        \Magento\Customer\Api\AddressRepositoryInterface $addressRepository,
        \Magento\Customer\Api\Data\AddressInterfaceFactory $addressFactory
	) {
		parent::__construct($context);
		
		$this->formKeyValidator = $formKeyValidator;
		$this->storeManager = $storeManager;
        $this->cookieManager = $cookieManager;
        $this->cookieMetadataFactory = $cookieMetadataFactory;
        $this->checkoutSession = $checkoutSession;
        $this->customerFactory = $customerFactory;
        $this->customerRepository = $customerRepository;
        $this->encryptor = $encryptor;
        $this->orderRepository = $orderRepository;
        $this->addressRepository = $addressRepository;
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
        if ($this->formKeyValidator->validate($this->getRequest()) === false) {
            $result['message'] = 'Invalid form key';
        } else {
            // check the session for guest order id set via observer
            if($guestOrderId > 0) {
                $order = $this->orderRepository->get($guestOrderId);
                
                try{
                    $customer = $this->customerRepository->get($order->getCustomerEmail());
                } catch(NoSuchEntityException $e) {
                    // do nothing
                }
                
                if (isset($customer) === true && $customer->getId()) {
                    $result['message'] = 'Account already exists.';
                } else {
                    if($password != null) {
                        try {
                            $customer = $this->customerFactory->create();
                            $customer->setWebsiteId($websiteId);
                            $customer->setEmail($order->getCustomerEmail());
                            $customer->setFirstname($order->getCustomerFirstName());
                            $customer->setLastname($order->getCustomerLastName());

                            $passwordHash = $this->encryptor->getHash($password, true);
                            $customer = $this->customerRepository->save($customer, $passwordHash);

                            //$customer->setConfirmation(null);
                            //$customer->save();

                            // update the order customer_id if the customer was created
                            if ($order->getId() && !$order->getCustomerId()) {
                                $order->setCustomerId($customer->getId());
                                $order->setCustomerIsGuest(0);
                                $this->orderRepository->save($order);
                            }

                            if($customer->getId()) {
                                $billingAddress = $order->getBillingAddress();
                                $shippingAddress = $order->getShippingAddress();
                                
                                // save the billing address
                                $customAddress = $this->addressFactory->create();
                                $customAddress->setCustomerId($customer->getId())
                                                ->setFirstname($billingAddress->getFirstName())
                                                ->setLastname($billingAddress->getLastname())
                                                ->setCountryId($billingAddress->getCountryId())
                                                ->setPostcode($billingAddress->getPostcode())
                                                ->setCity($billingAddress->getCity())
                                                ->setRegionId($billingAddress->getRegionId())
                                                ->setTelephone($billingAddress->getTelephone())
                                                ->setCompany($billingAddress->getCompany())
                                                ->setStreet($billingAddress->getStreet())
                                                ->setIsDefaultBilling(true)
                                                ->setIsDefaultShipping(false);
                                $this->addressRepository->save($customAddress);
                                
                                // save shipping address
                                $customAddress = $this->addressFactory->create();
                                $customAddress->setCustomerId($customer->getId())
                                                ->setFirstname($shippingAddress->getFirstName())
                                                ->setLastname($shippingAddress->getLastname())
                                                ->setCountryId($shippingAddress->getCountryId())
                                                ->setPostcode($shippingAddress->getPostcode())
                                                ->setCity($shippingAddress->getCity())
                                                ->setRegionId($shippingAddress->getRegionId())
                                                ->setTelephone($shippingAddress->getTelephone())
                                                ->setCompany($shippingAddress->getCompany())
                                                ->setStreet($shippingAddress->getStreet())
                                                ->setIsDefaultBilling(false)
                                                ->setIsDefaultShipping(true);
                                $this->addressRepository->save($customAddress);
                            }
                    
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