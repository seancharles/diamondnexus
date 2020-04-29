<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Company\Test\Block;

use Magento\Mtf\Block\Block;

/**
 * Company profile content block
 */
class ProfileContent extends Block
{
    /**
     * Selector for "Edit" button
     *
     * @var string
     */
    private $editButton = '.edit-company-profile';

    /**
     * Legal address section selector.
     *
     * @var string
     */
    private $legalAddressSection = '.block-company-profile-address';

    /**
     * Checks if legal address section is visible.
     *
     * @return bool
     */
    public function isLegalAddressSectionVisible()
    {
        return $this->_rootElement->find($this->legalAddressSection)->isVisible();
    }

    /**
     * Checks if "Edit" button is visible
     *
     * @return bool
     */
    public function isEditButtonVisible()
    {
        return $this->_rootElement->find($this->editButton)->isVisible();
    }

    /**
     * Clicks "Edit" button
     *
     * @return void
     */
    public function clickEditButton()
    {
        $this->_rootElement->find($this->editButton)->click();
    }
}
