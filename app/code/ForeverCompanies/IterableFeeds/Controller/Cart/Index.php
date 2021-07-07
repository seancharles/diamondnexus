<?php

namespace ForeverCompanies\IterableFeeds\Controller\Cart;

use Magento\Framework\Exception\NoSuchEntityException;

class Index extends \Magento\Framework\App\Action\Action
{
    protected $_logger;
    protected $_jsonResultFactory;
    protected $_urlInterface;
    protected $_quoteRepository;
    protected $_cart;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Controller\Result\JsonFactory $jsonResultFactory,
        \Magento\Framework\UrlInterface $urlInterface,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Magento\Checkout\Model\Cart $cart
    ) {
        parent::__construct($context);

        $this->_logger = $logger;
        $this->_jsonResultFactory = $jsonResultFactory;
        $this->_urlInterface = $urlInterface;
        $this->_quoteRepository = $quoteRepository;
    }

    public function execute()
    {
        $productsArray = [];

        $request = $this->getRequest()->getParams();

        $result = $this->_jsonResultFactory->create();

        if (isset($request['qid']) == true) {

            $quoteId = $request['qid'];
            
            //
            // $quoteId = $this->cart->getQuote()->getId();

            try {
                $quote = $this->_quoteRepository->get($quoteId);

                $storeId = $quote->getStoreId();
                $quoteItems = $quote->getAllVisibleItems();

                foreach ($quoteItems as $item) {

                    $product = $item->getProduct();

                    $productsArray[] = [
                        'id' => $item->getProductId(),
                        'name' => $item->getName(),
                        'price' => $product->getPriceInfo()->getPrice('regular_price')->getAmount()->getBaseAmount(),
                        'special_price' => $product->getFinalPrice(),
                        'img' => $product->getMediaConfig()->getMediaUrl($product->getImage()),
                        'url' => $product->getProductUrl(true)
                    ];
                }

                $result = $result->setData([
                    'url' => $this->_urlInterface->getUrl('checkout/cart/rebuild', ['id' => $quoteId]),
                    'products' => $productsArray
                ]);

            } catch (NoSuchEntityException $exception) {
                $this->_logger->error(
                    'Quote does not exist',
                    [$exception->getMessage()]
                );
            }
        }

        return $result;
    }
}