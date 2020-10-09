<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace ForeverCompanies\Salesforce\Block\Adminhtml\System\Config;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

/**
 * Class Disconnect
 * @package ForeverCompanies\Salesforce\Block\Adminhtml\System\Config
 */
class Disconnect extends Field
{
    /**
     * Get Auth Token Label
     *
     * @var string
     */
    protected $_disconnectButtonLabel = 'Disconnect';

    /**
     * @param $disconnectButtonLabel
     * @return $this
     */
    public function setButtonLabel($disconnectButtonLabel)
    {
        $this->_disconnectButtonLabel = $disconnectButtonLabel;
        return $this;
    }

    /**
     * Set template to itself
     *
     * @return \ForeverCompanies\Salesforce\Block\Adminhtml\System\Config\Disconnect
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if (!$this->getTemplate()){
            $this->setTemplate('system/config/connection/disconnect.phtml');
        }

        return $this;
    }

    /**
     * Unset some non-related element parameters
     *
     * @param  \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    public function render(AbstractElement $element)
    {
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();
        return parent::render($element);
    }

    /**
     * Get the
     */
}
