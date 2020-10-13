<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace ForeverCompanies\Salesforce\Block\System\Config\Form\Field;

use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Config\Block\System\Config\Form\Field;

/**
 * Class Password: hide pass in Configuration
 *
 * @package ForeverCompanies\Salesforce\Block\System\Config\Form\Field
 */
class Password extends Field
{
    /**
     * Add Attribute password input
     *
     * @param  AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        $element->setType('password');
        return $element->getElementHtml();
    }
}
