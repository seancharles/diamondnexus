<?php

namespace ForeverCompanies\CustomAttributes\Model\Observer;

use Cloudinary\Cloudinary\Helper\Product\Free as Helper;
use Cloudinary\Cloudinary\Model\TransformationFactory;
use ForeverCompanies\CustomAttributes\Helper\Media;
use Magento\Catalog\Model\Product;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class SaveMediaFields implements ObserverInterface
{
    /**
     * @var Media
     */
    private $helper;

    /**
     * @method __construct
     * @param Media $helper
     */
    public function __construct(
        Media $helper
    ) {
        $this->helper = $helper;
    }

    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        $product = $observer->getData('product');
        $mediaGalleryImages = $this->getMediaGalleryImages($product);
        $this->helper->saveFieldsToMedia($mediaGalleryImages);
    }

    /**
     * @param  Product $product
     * @return array
     */
    protected function getMediaGalleryImages(Product $product)
    {
        $mediaGallery = $product->getData('media_gallery');

        if (!$mediaGallery || !array_key_exists('images', $mediaGallery)) {
            return [];
        }

        return $mediaGallery['images'];
    }
}
