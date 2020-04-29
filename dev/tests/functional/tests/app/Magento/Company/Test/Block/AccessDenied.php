<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Company\Test\Block;

use Magento\Cms\Test\Fixture\CmsPage;
use Magento\Mtf\Block\Block;

/**
 * Block for company access denied page.
 */
class AccessDenied extends Block
{
    /**
     * Heading block selector.
     *
     * @var string
     */
    private $headingBlock = '.page-title span';

    /**
     * Content block selector.
     *
     * @var string
     */
    private $contentBlock = '.column.main';

    /**
     * Get content heading.
     *
     * @return string
     */
    public function getContentHeading()
    {
        return trim($this->_rootElement->find($this->headingBlock)->getText());
    }

    /**
     * Get page content.
     *
     * @return string
     */
    public function getPageContent()
    {
        return trim($this->_rootElement->find($this->contentBlock)->getText());
    }
}
