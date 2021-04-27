<?php
/**
 * Copyright © ForeverCompanies LLC. All rights reserved.
 * See COPYING.txt for license details.
 * http://www.forevercompanies.com | support@forevercompanies.com
 */

namespace ForeverCompanies\LinkProduct\Ui\DataProvider\Product\Related;

use Magento\Catalog\Ui\DataProvider\Product\Related\AbstractDataProvider;

class AccessoryDataProvider extends AbstractDataProvider
{
    /**
     * {@inheritdoc
     */
    protected function getLinkType()
    {
        return 'accessory';
    }
}
