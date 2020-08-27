<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace ForeverCompanies\CustomAttributes\Model\Observer;

use Cloudinary\Cloudinary\Model\Observer\ProductGalleryChangeTemplate as OriginalObserver;
use ForeverCompanies\CustomAttributes\Block\Adminhtml\Product\Helper\Form\Gallery\Content;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class ProductGalleryChangeTemplate extends OriginalObserver implements ObserverInterface
{

    /**
     * @param Observer $observer
     * @return ProductGalleryChangeTemplate
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute(Observer $observer)
    {
        if (!$this->configuration->isEnabled()) {
            return $this;
        }
        $observer->getBlock()->setTemplate(Content::TEMPLATE_GALLERY_PHTML);
    }
}
