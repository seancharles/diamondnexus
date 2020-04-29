<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SharedCatalog\Test\TestCase;

use Magento\Mtf\TestCase\Injectable;
use Magento\SharedCatalog\Test\Page\Adminhtml\SharedCatalogConfigure;
use Magento\SharedCatalog\Test\Page\Adminhtml\SharedCatalogIndex;
use Magento\Catalog\Test\Fixture\CatalogProductSimple;
use Magento\SharedCatalog\Test\Fixture\SharedCatalog;

/**
 * Abstract configuration test
 */
abstract class AbstractSharedCatalogConfigurationTest extends Injectable
{
    /**
     * @var SharedCatalogIndex $sharedCatalogIndex
     */
    protected $sharedCatalogIndex;

    /**
     * @var SharedCatalogConfigure $sharedCatalogConfigure
     */
    protected $sharedCatalogConfigure;

    /**
     * Open Catalog configuration
     *
     * @param string $catalogName
     * @return void
     */
    protected function openConfiguration($catalogName)
    {
        $this->sharedCatalogIndex->getGrid()->search(['name' => $catalogName]);
        $this->sharedCatalogIndex->getGrid()->openConfigure($this->sharedCatalogIndex->getGrid()->getFirstItemId());
    }

    /**
     * Gets method name.
     *
     * @param string $step
     * @return string
     */
    protected function getMethodName($step)
    {
        $step = explode('_', $step);
        $method = '';
        foreach ($step as $value) {
            if ($value) {
                $value = ucfirst($value);
            }
            $method .= $value;
        }

        return $method;
    }
}
