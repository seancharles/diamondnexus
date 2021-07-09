<?php

namespace ForeverCompanies\IterableFeeds\Controller\Cart;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\UrlInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Psr\Log\LoggerInterface;

class Index extends Action
{
    protected $_logger;
    protected $_jsonResultFactory;
    protected $_urlInterface;
    protected $_quoteRepository;
    protected $_cart;

    public function __construct(
        Context $context,
        LoggerInterface $logger,
        JsonFactory $jsonResultFactory,
        UrlInterface $urlInterface,
        CartRepositoryInterface $quoteRepository
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

            $quoteId = (int)$request['qid'];

            try {
                $quote = $this->_quoteRepository->get($quoteId);

                $quoteItems = $quote->getAllVisibleItems();

                foreach ($quoteItems as $item) {

                    $product = $item->getProduct();

                    $productsArray[] = [
                        'id' => $item->getData('product_id'),
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

                $result = $result->setData([
                    'message' => "Unable to find quote"
                ]);

                $this->_logger->error(
                    'Quote does not exist',
                    [$exception->getMessage()]
                );
            }
        }

        return $result;
    }
}
