<?php
namespace Progressive\PayWithProgressive\Controller\Payment;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Quote\Api\CartManagementInterface;
use Magento\Checkout\Model\Session;
use Progressive\PayWithProgressive\Model\Checkout;
use Magento\Framework\Exception\LocalizedException;
use Progressive\PayWithProgressive\Model\Config as ProgressiveConfig;
use Magento\Sales\Model\Order\Email\Sender\OrderSender;

class Finalize extends Action
{
	protected $quoteRepository;
	protected $quote;
	protected $config;
	protected $quoteManagement;
	protected $orderSender;
	protected $quoteId;

	public function __construct(
		Context $context,
		\Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
			CartManagementInterface $quoteManager,
			ProgressiveConfig $config,
			OrderSender $orderSender
	) {
		$this->quoteRepository = $quoteRepository;
		$this->config = $config;
		$this->quoteManagement = $quoteManager;
		$this->orderSender = $orderSender;
		parent::__construct($context);
	}

	public function execute()
	{
		$params = json_decode($this->getRequest()->getContent(), true);
		$this->quoteId = str_replace($this->config->getValue("merchant_id"), "", $params["cartId"]);
		$leaseId = $params["leaseId"];

		$this->quote = $this->quoteRepository->getActive($this->quoteId);

		$this->place($leaseId);
	}

	public function place($leaseId)
	{
		if (!$this->quote->getGrandTotal()) {
			throw new \Magento\Framework\Exception\LocalizedException(
				__(
					'Progressive can\'t process orders with a zero balance due. '
					. 'To finish your purchase, please go through the standard checkout process.'
				)
			);
		}
		if (!$leaseId) {
			throw new \Magento\Framework\Exception\LocalizedException(
				__(
					'LeaseId not found in lease verification response.'
				)
			);
		}

		$this->prepareGuestQuote();
        $this->setLeaseId($leaseId);
        $this->quoteManagement->placeOrder($this->quoteId);

		http_response_code(200);
	}

	protected function setLeaseId($token)
	{
		if ($token) {
			$payment = $this->quote->getPayment();
			$payment->setAdditionalInformation('lease_id', $token);
			$payment->save();
		}
	}

	protected function prepareGuestQuote()
	{
		$quote = $this->quote;
		if($quote->getCustomerId() == null)
		{
			$quote->setCustomerEmail($quote->getBillingAddress()->getEmail())
				->setCustomerIsGuest(true)
				->setCustomerGroupId(\Magento\Customer\Model\Group::NOT_LOGGED_IN_ID);
		}
		return $this;
	}
}
