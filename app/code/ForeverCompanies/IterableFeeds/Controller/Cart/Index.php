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
use Magento\Catalog\Helper\Image;
use Psr\Log\LoggerInterface;

class Index extends Action
{
    protected $logger;
    protected $jsonResultFactory;
    protected $urlInterface;
    protected $productRepository;
    protected $quoteRepository;
    protected $attributeRepository;
    protected $helperImage;

    public function __construct(
        Context $context,
        LoggerInterface $logger,
        JsonFactory $jsonResultFactory,
        UrlInterface $urlInterface,
        ProductRepositoryInterface $productRepository,
        CartRepositoryInterface $quoteRepository,
        ScopeConfigInterface $scopeConfig,
        Repository $attributeRepository,
        Image $helperImage
    ) {
        parent::__construct($context);

        $this->logger = $logger;
        $this->jsonResultFactory = $jsonResultFactory;
        $this->urlInterface = $urlInterface;
        $this->productRepository = $productRepository;
        $this->quoteRepository = $quoteRepository;
        $this->attributeRepository = $attributeRepository;
        $this->helperImage = $helperImage;
    }

    public function execute ()
    {
        $productsArray = [];
        $metalMap = [];

        $request = $this->getRequest()->getParams();
        $result = $this->jsonResultFactory->create();

        if (isset($request['qid']) == true) {
            $quoteId = (int) $request['qid'];

            try {
                $metalOptions = $this->attributeRepository->get('metal_type')->getOptions();

                foreach ($metalOptions as $metalOption) {
                    $metalMap[$metalOption->getValue()] = strtolower($metalOption->getLabel());
                }

                $quote = $this->quoteRepository->get($quoteId);
                $quoteItems = $quote->getAllVisibleItems();

                foreach ($quoteItems as $item) {
                    $images = [];
                    $product = $this->productRepository->getById($item->getProductId());
                    $imageGallery = $product->getMediaGalleryImages();

                    $configOptions = $item->getBuyRequest()->getSuperAttribute();

                    if (isset($configOptions[145]) === true) {
                        foreach ($imageGallery as $image) {
                            $label = strtolower($image->getLabel());

                            if (strlen($label) > 0) {
                                if (strpos($label, "default") !== false) {
                                    $metalType = $metalMap[$configOptions[145]];

                                    if (strpos($label, $metalType) !== false) {
                                        $images[] = $image->getUrl();
                                    }
                                }
                            }
                        }
                    }

                    // used as a default in case a tagged image isn't found
                    foreach ($imageGallery as $image) {
                        $images[] = $image->getUrl();
                        break;
                    }

                    // handling for no images found in gallery
                    if (count($images) ==0) {
                        $images[] = $this->helperImage->getDefaultPlaceholderUrl();
                    }

                    $productsArray[] = [
                        'id' => $item->getData('product_id'),
                        'name' => $item->getName(),
                        'price' => $product->getPriceInfo()->getPrice('regular_price')->getAmount()->getBaseAmount(),
                        'special_price' => $product->getFinalPrice(),
                        'img' => $this->formatCloudinaryImagePath($images[0]),
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

    protected function formatCloudinaryImagePath($path = null, $width = 0, $quality = 90)
    {
        $host = 'https://res-2.cloudinary.com/foco/image/upload/';

        if (strpos($path, $host) !== false) {
            $folderPosition = strpos($path, "/v1/media");

            // get the cloudinary parameters from uri
            $params = substr($path, strlen($host), $folderPosition - strlen($host));

            // get the actual path to the file from uri
            $file = substr($path, $folderPosition);

            // return uri with modified params
            return $host . $params . ",w_200" . $file;
        } else {
            return $path;
        }
    }
}
