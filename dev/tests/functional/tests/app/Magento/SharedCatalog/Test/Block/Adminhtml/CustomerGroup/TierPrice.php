<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SharedCatalog\Test\Block\Adminhtml\CustomerGroup;

use Magento\Mtf\Block\Block;

/**
 * Product advanced price settings tier price block.
 */
class TierPrice extends Block
{
    /**
     * Css selector tier price option.
     *
     * @var string
     */
    private $tierPriceOption = '[data-index="tier_price"] tbody tr';

    /**
     * Css selector shared catalog option name.
     *
     * @var string
     */
    private $sharedCatalogNameOptionValue = '.admin__action-multiselect-text';

    /**
     * Css selector tier price option remove row button.
     *
     * @var string
     */
    private $tierPriceOptionDeleteButton = '[data-action="remove_row"]';

    /**
     * Set Customer Group tier price data.
     *
     * @param array $options
     * @return void
     */
    public function setOptions(array $options)
    {
        /** @var \Magento\Catalog\Test\Block\Adminhtml\Product\Edit\Section\AdvancedPricing\OptionTier $optionsForm */
        $optionsForm = $this->blockFactory->create(
            '\Magento\Catalog\Test\Block\Adminhtml\Product\Edit\Section\AdvancedPricing\OptionTier',
            ['element' => $this->_rootElement]
        );

        $optionsForm->fillOptions(
            $options,
            $this->_rootElement
        );
    }

    /**
     * Is tier price options present in advanced price settings section.
     *
     * @return bool
     */
    public function isTierPriceOptionsPresent()
    {
        $options = $this->_rootElement->getElements($this->tierPriceOption);

        return (bool)count($options);
    }

    /**
     * Is tier price option present in advanced price settings section.
     *
     * @param string $sharedCatalogName
     * @return bool
     */
    public function isTierPriceOptionPresent($sharedCatalogName)
    {
        $options = $this->_rootElement->getElements($this->tierPriceOption);

        foreach ($options as $option) {
            $sharedCatalogOptionValue = trim($option->find($this->sharedCatalogNameOptionValue)->getText());

            if ($sharedCatalogOptionValue == $sharedCatalogName) {
                return true;
            }
        }

        return false;
    }

    /**
     * Remove all tier price options.
     *
     * @return void
     */
    public function removeAllTierPriceOptions()
    {
        $options = $this->_rootElement->getElements($this->tierPriceOption);

        foreach ($options as $option) {
            $option->find($this->tierPriceOptionDeleteButton)->click();
        }
    }
}
