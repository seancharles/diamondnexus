<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CompanyCredit\Test\Block\Adminhtml;

use Magento\Mtf\Block\Block;
use Magento\Mtf\Client\Locator;
use Magento\Backend\Test\Block\Template;

/**
 * Block contains information with outstanding balance, available credit and credit limit.
 */
class CreditBalanceInformation extends Block
{
    /**
     * Outstanding balance index.
     */
    const ITEM_OUTSTANDING_BALANCE = 1;

    /**
     * Available credit index.
     */
    const ITEM_AVAILABLE_CREDIT = 2;

    /**
     * Credit limit index.
     */
    const ITEM_CREDIT_LIMIT = 3;

    /**
     * Backend abstract block.
     *
     * @var string
     */
    private $templateBlock = './ancestor::body';

    /**
     * Css locator for credit balance item value.
     *
     * @var string
     */
    private $balanceValueSelector = '[data-role="credit-balance"] '
                                        . 'li.credit-balance-item:nth-child(%d) .credit-balance-price';

    /**
     * Mapping of balance item codes to indexes.
     *
     * @var array
     */
    private $itemIndexMapping = [
        'outstandingBalance' => self::ITEM_OUTSTANDING_BALANCE,
        'availableCredit' => self::ITEM_AVAILABLE_CREDIT,
        'creditLimit' => self::ITEM_CREDIT_LIMIT,
    ];

    /**
     * Get value by index from credit balance list.
     *
     * @param string $key
     * @return float|null
     */
    public function getCreditBalanceValue($key)
    {
        if (!isset($this->itemIndexMapping[$key])) {
            return null;
        }
        $this->getTemplateBlock()->waitLoader();
        $value = $this->_rootElement
            ->find(sprintf($this->balanceValueSelector, $this->itemIndexMapping[$key]))
            ->getText();
        return $value === '' ? null : (float)preg_replace("/[^\-\.0-9]/", "", $value);
    }

    /**
     * Get backend abstract block.
     *
     * @return Template
     */
    private function getTemplateBlock()
    {
        return $this->blockFactory->create(
            \Magento\Backend\Test\Block\Template::class,
            ['element' => $this->_rootElement->find($this->templateBlock, Locator::SELECTOR_XPATH)]
        );
    }
}
