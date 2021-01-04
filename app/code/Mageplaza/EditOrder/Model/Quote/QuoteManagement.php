<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_EditOrder
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\EditOrder\Model\Quote;

use Magento\Quote\Model\Quote as QuoteEntity;

/**
 * Class QuoteManagement
 * @package Mageplaza\EditOrder\Model\Quote
 */
class QuoteManagement extends \Magento\Quote\Model\QuoteManagement
{
    /**
     * Get items from quote items
     *
     * @param QuoteEntity $quote
     *
     * @return array|mixed
     */
    public function getResolveItems(QuoteEntity $quote)
    {
        return $this->resolveItems($quote);
    }
}
