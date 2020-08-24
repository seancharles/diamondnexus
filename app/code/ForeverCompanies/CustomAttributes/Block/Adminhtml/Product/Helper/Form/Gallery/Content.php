<?php

namespace ForeverCompanies\CustomAttributes\Block\Adminhtml\Product\Helper\Form\Gallery;

/**
 * Block for gallery content.
 */
class Content extends \Cloudinary\Cloudinary\Block\Adminhtml\Product\Helper\Form\Gallery\Content
{

    /** @var string TODO: add functions for show 3 new fields in phtml */

    const TEMPLATE_GALLERY_PHTML = 'ForeverCompanies_CustomAttributes::catalog/product/helper/gallery.phtml';
    /**
     * @var string
     */
    protected $_template = self::TEMPLATE_GALLERY_PHTML;
}
