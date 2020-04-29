<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SharedCatalog\Test\Block\Adminhtml\SystemConfig;

use Magento\Mtf\Block\Block;
use Magento\Mtf\Client\Locator;

/**
 * Block for system configuration B2B features.
 */
class BtobFeatures extends Block
{
    /**
     * Css selector for system configuration B2B section.
     *
     * @var string
     */
    private $sectionDescription = '#btob_website_configuration .comment';

    /**
     * Css selector for system configuration B2B section Shared Catalog item comment.
     *
     * @var string
     */
    private $sharedCatalogItemComment = '#row_btob_website_configuration_sharedcatalog_active .value .note span';

    /**
     * Css selector for system configuration B2B section Enable Shared Catalog.
     *
     * @var string
     */
    private $enableSharedCatalogFeature = '#btob_website_configuration_sharedcatalog_active';

    /**
     * Shared Catalog feature enabled option label.
     *
     * @var string
     */
    private $sharedCatalogEnabledValue = 'Yes';

    /**
     * Get system configuration B2B section description.
     *
     * @return string
     */
    public function getSectionDescription()
    {
        return trim($this->_rootElement->find($this->sectionDescription)->getText());
    }

    /**
     * Get system configuration B2B section Shared Catalog item comment.
     *
     * @return string
     */
    public function getSharedCatalogItemComment()
    {
        return trim($this->_rootElement->find($this->sharedCatalogItemComment)->getText());
    }

    /**
     * Enable Shared Catalog B2B feature.
     *
     * @return void
     */
    public function enableSharedCatalogFeature()
    {
        $this->_rootElement->find($this->enableSharedCatalogFeature, Locator::SELECTOR_CSS, 'select')
            ->setValue($this->sharedCatalogEnabledValue);
    }
}
