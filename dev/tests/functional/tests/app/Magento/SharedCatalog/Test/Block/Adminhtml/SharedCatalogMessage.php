<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SharedCatalog\Test\Block\Adminhtml;

use Magento\Mtf\Block\Block;

/**
 * Shared Catalog feature disabled message block.
 */
class SharedCatalogMessage extends Block
{
    /**
     * Link to B2B features in system configuration.
     *
     * @var string
     */
    private $featuresLink = 'a';

    /**
     * Open system configuration B2B features page.
     *
     * @return void
     */
    public function openConfigurationFeaturesPage()
    {
        $this->_rootElement->find($this->featuresLink)->click();
    }
}
