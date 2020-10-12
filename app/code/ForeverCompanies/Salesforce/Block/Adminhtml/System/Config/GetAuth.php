<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace ForeverCompanies\Salesforce\Block\Adminhtml\System\Config;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Tests\NamingConvention\true\string;

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
    protected $email = 'salesforcecrm_config_email';

    /**
     * Password
     *
     * @var string
     */
    protected $password = 'salesforcecrm_config_passwd';

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




}
