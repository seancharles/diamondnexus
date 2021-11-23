<?php

namespace ForeverCompanies\IterableCore\Controller\Cart;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

class Rebuild extends \Magento\Framework\App\Action\Action
{
    protected $_logger;
    protected $_urlInterface;
    protected $_cart;
    protected $_productFactory;
    protected $_quoteRepository;
    protected $_cartItemRepository;
    protected $_redirectFactory;
    protected $_messageManager;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\UrlInterface $urlInterface,
        \Magento\Checkout\Model\Cart $cart,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Magento\Quote\Api\CartItemRepositoryInterface $cartItemRepository,
        \Magento\Framework\Controller\Result\RedirectFactory $redirectFactory,
        \Magento\Framework\Message\ManagerInterface $messageManager
    ) {
        parent::__construct($context);

        $this->_logger = $logger;
        $this->_urlInterface = $urlInterface;
        $this->_cart = $cart;
        $this->_productFactory = $productFactory;
        $this->_quoteRepository = $quoteRepository;
        $this->_cartItemRepository = $cartItemRepository;
        $this->_redirectFactory = $redirectFactory;
        $this->_messageManager = $messageManager;
    }

    public function execute()
    {
        $quoteId = (int) $this->getRequest()->getParam('id');
        
        if ($quoteId > 0) {
            // get the customers quote id
            $customerQuoteId = $this->_cart->getQuote()->getId();

            // only add items when the quote isn't the users logged in cart

            $writer = new \Zend\Log\Write\Stream(BP . 'var/log/rebuilt_cart.log');
            $logger = new \Zend\Log\Logger();
            $logger->addWriter($writer);
            $logger->info('Quote Id: ' . $quoteId);
            $logger->info('Customer Quote Id: ' . $quoteId);
            if ($quoteId != $customerQuoteId) {
                try {
                    $quote = $this->_quoteRepository->get($quoteId);
                    if(empty($quote)) {
                        $logger->info('Quote is empty');
                    }
                    $storeId = $quote->getStoreId();
                    $quoteItems = $quote->getAllVisibleItems();

                    foreach ($quoteItems as $item) {
                        $productId = $item->getProductId();
                        $buyRequest = $item->getBuyRequest();

                        $_product = $this->_productFactory->create()
                            ->setStoreId($storeId)
                            ->load($productId);

                        $options = [];

                        $params = [
                            'product' => $productId,
                            'super_attribute' => $buyRequest['super_attribute'],
                            'options' => $buyRequest['options'],
                            'qty' => $item->getQty()
                        ];

                        $this->_cart->addProduct($_product, $params);

                        /*
                         *  TODO: Update to use cartItemInterface
                         *
                            $cartItem = $this->_cartItemInterfaceFactory->create(['data' => [
                                \Magento\Quote\Api\Data\CartItemInterface::KEY_SKU      => $item->getSku(),
                                \Magento\Quote\Api\Data\CartItemInterface::KEY_QTY      => 1,
                                \Magento\Quote\Api\Data\CartItemInterface::KEY_QUOTE_ID => $customerQuoteId
                            ]]);

                            $this->_cartItemRepository->save($cartItem);
                        */
                    }

                    $this->_cart->getQuote()->collectTotals();
                    $this->_cart->save();

                    $redirect = $this->_redirectFactory->create();
                    $redirect->setPath('checkout/cart');
                    return $redirect;

                } catch (NoSuchEntityException $exception) {
                    $this->_messageManager->addError(__("Quote does not exist"));

                    $this->_logger->error(
                        'Quote does not exist',
                        [$exception->getMessage()]
                    );

                    $redirect = $this->_redirectFactory->create();
                    $redirect->setPath('checkout/cart');
                    return $redirect;
                } catch (LocalizedException $exception) {
                    $this->_messageManager->addError(__("Could not add item to cart"));

                    $this->_logger->error(
                        'Could not add item to cart',
                        [$exception->getMessage()]
                    );

                    $redirect = $this->_redirectFactory->create();
                    $redirect->setPath('checkout/cart');
                    return $redirect;
                }
            }
        }
    }
}