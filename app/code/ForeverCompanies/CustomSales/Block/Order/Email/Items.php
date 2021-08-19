<?php

namespace ForeverCompanies\CustomSales\Block\Order\Email;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\View\Element\Template\Context;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;

use Magento\Framework\App\State;
use ForeverCompanies\LinkProduct\Model\Accessory;
use Magento\Catalog\Api\ProductRepositoryInterface;

class Items extends \Magento\Sales\Block\Order\Email\Items
{
    protected $state;
    protected $accessory;
    protected $productRepository;

    /**
     * @param Context $context
     * @param array $data
     * @param OrderRepositoryInterface|null $orderRepository
     */
    public function __construct(
        Context $context,
        OrderRepositoryInterface $orderRepository,
        State $state,
        Accessory $accessory,
        ProductRepositoryInterface $productRepository,
        array $data = []
    ) {
        parent::__construct($context, $data, $orderRepository);
        
        $this->state = $state;
        $this->accessory = $accessory;
        $this->productRepository = $productRepository;
    }
    
    public function getCrossSellProducts($product) {
        $result = [];

        $crossSellProductList = $this->accessory->getAccessoryProductIds($product);

        foreach($crossSellProductList as $crossSellProductId) {
            $images = [];
            $product = $this->productRepository->getById($crossSellProductId);

            $metalType = $product->getResource()->getAttribute('metal_type')->getStoreLabel();

            if(!$metalType) {
                $metalType = "14k White Gold";
            }

            $imageGallery = $product->getMediaGalleryImages();

            foreach($imageGallery as $image) {
                $label = strtolower($image->getLabel());

                if(strlen($label) > 0) {
                    if(strpos($label,"default") !== false) {
                        if(strpos($label, $metalType) !== false) {
                            $images[] = $image->getUrl();
                        }
                    }
                }
            }

            // used as a default in case a tagged image isn't found
            foreach($imageGallery as $image) {
                $images[] = $image->getUrl();
                break;
            }

            $result[] = [
                'id' => $product->getId(),
                'name' => $product->getName(),
                'url' => $product->getProductUrl(true),
                'image' => $this->formatCloudinaryImagePath($images[0])
            ];
        }


        return $result;
    }

    protected function formatCloudinaryImagePath($path = null, $width = 0, $quality = 90)
    {
        $host = 'https://res-2.cloudinary.com/foco/image/upload/';

        if(strpos($path, $host) !== false) {
            $folderPosition = strpos($path,"/v1/media");

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