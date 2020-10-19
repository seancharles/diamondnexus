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
 * Class GetAuth
 *
 * @package ForeverCompanies\Salesforce\Block\Adminhtml\System\Config
 */
class GetAuth extends Field
{
    /**
     * Email
     *
     * @var string
     */
    protected $email = 'salesforcecrm_salesforceconfig_email';

    /**
     * Password
     *
     * @var string
     */
    protected $password = 'salesforcecrm_salesforceconfig_passwd';

    /**
     * Get Auth Token Label
     *
     * @var string
     */
    protected $authButtonLabel = 'Get Access Token';

    /**
     * Set Email SalesforceCRM
     *
     * @param $email
     * @return $this
     */
    public function setEmail($email)
    {
        $this->email = $email;
        return $this;
    }

    /**
     * Get Email Salesforce
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set password
     *
     * @param $password
     * @return $this
     */
    public function setPasswd($password)
    {
        $this->password = $password;
        return $this;
    }

    /**
     * Get Password
     *
     * @return string
     */
    public function getPasswd()
    {
        return $this->password;
    }

    /**
     * Set Get Auth Token Button label
     *
     * @param $getAuthButtonLabel
     * @return $this
     */
    public function setButtonLabel($getAuthButtonLabel)
    {
        $this->authButtonLabel = $getAuthButtonLabel;
        return $this;
    }

    /**
     * Set template to itself
     *
     * @return \ForeverCompanies\Salesforce\Block\Adminhtml\System\Config\GetAuth
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if (!$this->getTemplate()) {
            $this->setTemplate('system/config/getauth.phtml');
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
     * Get the button and scripts contents
     *
     * @param  \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        $originalData = $element->getOriginalData();
        $buttonLabel  = !empty($originalData['button_label']) ? $originalData['button_label'] : $this->_authButtonLabel;
        $this->addData(
            [
                'button_label' => __($buttonLabel),
                'html_id'      => $element->getHtmlId(),
                'ajax_url'     => $this->_urlBuilder->getUrl('salesforce/system_config_getauth/getAuth'),
            ]
        );

        return $this->_toHtml();
    }





}
