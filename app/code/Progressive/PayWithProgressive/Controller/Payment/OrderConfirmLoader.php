<?php

/**
 * Astound
 * NOTICE OF LICENSE
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to codemaster@astoundcommerce.com so we can send you a copy immediately.
 *
 * @category  Affirm
 * @package   Astound_Affirm
 * @copyright Copyright (c) 2016 Astound, Inc. (http://www.astoundcommerce.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Copyright (c) 2016 Astound, Inc. (http://www.astoundcommerce.com).
 * Modified by Prog Leasing, LLC. Copyright (c) 2018, Prog Leasing, LLC.
 */

namespace Progressive\PayWithProgressive\Controller\Payment;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Quote\Api\CartManagementInterface;
use Magento\Checkout\Model\Session;
use Progressive\PayWithProgressive\Model\Checkout;
use Progressive\PayWithProgressive\Model\Config as ProgressiveConfig;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class Confirm
 *
 * @package Progressive\PayWithProgressive\Controller\Payment
 */
class OrderConfirmLoader extends Action
{
    protected $checkoutSession;
    protected $quote;
    protected $quoteFactory;
    protected $orderRepository;
    protected $searchCriteriaBuilder;

    protected $config;
    protected $maxWait = 30;
    protected $waitStep = 2;

    protected $logger;

    public function __construct(
        Context $context,
        Session $checkoutSession,
    \Magento\Quote\Model\QuoteFactory $quoteFactory,
    \Magento\Sales\Model\OrderRepository $orderRepository,
    \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
    ProgressiveConfig $config,
        \Progressive\PayWithProgressive\Logger\Logger $logger
    ) {
        $this->checkoutSession = $checkoutSession;
    $this->quote = $checkoutSession->getQuote();
    $this->quoteFactory = $quoteFactory;
    $this->orderRepository = $orderRepository;
    $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    $this->config = $config;
        $this->logger = $logger;
        parent::__construct($context);
    }

    public function execute()
    {
        try
        {
            $quoteId = str_replace($this->config->getValue("merchant_id"), "", $this->getRequest()->getParam('compositeId'));
            $this->quote = $this->quoteFactory->create()->load($quoteId);

            $this->checkoutSession->clearHelperData();
            $this->checkoutSession->setLastQuoteId($quoteId)->setLastSuccessQuoteId($quoteId);

            $searchCriteria = $this->searchCriteriaBuilder->addFilter('increment_id', $this->quote->getReservedOrderId(), 'eq')->create();
            $orderList = $this->orderRepository->getList($searchCriteria)->getItems();
            
            $waitCount = 0;
            while(count($orderList) == 0)
            {
                sleep($this->waitStep);
                $orderList = $this->orderRepository->getList($searchCriteria)->getItems();
                $waitCount++;
                if($waitCount > $this->maxWait/$this->waitStep)
                {
                    $this->messageManager->addErrorMessage("Order could not be confirmed. Please contact the site administrator");
                    $this->logConfirm('ERROR confirming ');
                    $this->_redirect('checkout/cart');
                    return;
                }
            }
            $order = reset($orderList);

            $this->checkoutSession->setLastOrderId($order->getId())
                ->setLastRealOrderId($order->getIncrementId())
                ->setLastOrderStatus($order->getStatus());
            $this->_eventManager->dispatch(
                'progressive_place_order_success',
                ['order'=>$order, 'quote' =>$this->quote]);
            $this->logConfirm('Success confirming: ');
            $this->_redirect('checkout/onepage/success');
            return;

        }catch (LocalizedException $e) {
                $this->messageManager->addExceptionMessage(
                    $e,
                    $e->getMessage()
                );
                $this->logConfirm('ERROR: exception confirming: ' . $e->getMessage());
                $this->_redirect('checkout/cart');
        } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage(
                    $e,
                    __('We can\'t place the order.')
                );
                $this->logConfirm('ERROR: exception confirming: ' . $e->getMessage());
                $this->_redirect('checkout/cart');
        }
    }

    private function logConfirm($message) {
        $compIdText = "CompositeId: {$this->getRequest()->getParam('compositeId')}";
        $leaseIdText = "LeaseId: {$this->getRequest()->getParam('leaseID')}";
        $this->logger->info("{$message} ({$leaseIdText} / {$compIdText})");
    }
}
