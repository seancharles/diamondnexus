<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace ForeverCompanies\Salesforce\Block\System\Config\Form\Field;

use Magento\Config\Block\System\Config\Form\Field as ConfigFormField;
use ForeverCompanies\Salesforce\Block\Adminhtml\Connection\Status as ConnectionStatus;

class Connection extends ConfigFormField
{
    /**
     * Create element for Access token field in store configuration
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $connectionHtml = $this->getLayout()->createBlock(ConnectionStatus::class)->toHtml();
        return $element->getElementHtml() . $connectionHtml;
    }
}
