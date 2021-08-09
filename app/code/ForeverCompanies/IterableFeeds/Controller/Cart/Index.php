<?php

namespace ForeverCompanies\IterableFeeds\Controller\Cart;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\UrlInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Catalog\Model\Product\Attribute\Repository;
use Psr\Log\LoggerInterface;

class Index extends Action
{
    protected $logger;
    protected $jsonResultFactory;
    protected $urlInterface;
    protected $productRepository;
    protected $quoteRepository;
    protected $attributeRepository;

    public function __construct(
        Context $context,
        LoggerInterface $logger,
        JsonFactory $jsonResultFactory,
        UrlInterface $urlInterface,
        ProductRepositoryInterface $productRepository,
        CartRepositoryInterface $quoteRepository,
        Repository $attributeRepository
    )
    {
        parent::__construct($context);

        $this->logger = $logger;
        $this->jsonResultFactory = $jsonResultFactory;
        $this->urlInterface = $urlInterface;
        $this->productRepository = $productRepository;
        $this->quoteRepository = $quoteRepository;
        $this->attributeRepository = $attributeRepository;
    }

    public function execute()
    {
        $productsArray = [];
        $metalMap = [];

        $request = $this->getRequest()->getParams();
        $result = $this->jsonResultFactory->create();

        if (isset($request['qid']) == true) {
            $quoteId = (int) $request['qid'];

            try {
                $metalOptions = $this->attributeRepository->get('metal_type')->getOptions();

                foreach($metalOptions as $metalOption) {
                    $metalMap[$metalOption->getValue()] = strtolower($metalOption->getLabel());
                }

                $quote = $this->quoteRepository->get($quoteId);
                $quoteItems = $quote->getAllVisibleItems();

                foreach ($quoteItems as $item) {
                    $images = [];
                    $product = $this->productRepository->getById($item->getProductId());

                    $configOptions = $item->getBuyRequest()->getSuperAttribute();

                    if(isset($configOptions[145]) === true) {
                        $imageGallery = $product->getMediaGalleryImages();

                        foreach($imageGallery as $image) {
                            $label = strtolower($image->getLabel());

                            if(strlen($label) > 0) {
                                if(strpos($label,"default") !== false) {
                                    $metalType = $metalMap[$configOptions[145]];

                                    if(strpos($label, $metalType) !== false) {
                                        $images[] = $image->getUrl();
                                    }
                                }
                            }
                        }
                    }

                    $images[] = $item->getProduct()->getMediaConfig()->getMediaUrl(
                        $item->getProduct()->getImage()
                    );

                    $productsArray[] = [
                        'id' => $item->getData('product_id'),
                        'name' => $item->getName(),
                        'price' => $product->getPriceInfo()->getPrice('regular_price')->getAmount()->getBaseAmount(),
                        'special_price' => $product->getFinalPrice(),
                        'img' => $images[0],
                        'url' => $product->getProductUrl(true)
                    ];
                }

                $result = $result->setData([
                    'url' => $this->urlInterface->getUrl('checkout/cart/rebuild', ['id' => $quoteId]),
                    'products' => $productsArray
                ]);

            } catch (NoSuchEntityException $exception) {

                $result = $result->setData([
                    'message' => "Unable to find quote"
                ]);

                $this->logger->error(
                    'Quote does not exist',
                    [$exception->getMessage()]
                );
            }
        }

        return $result;
    }
}
