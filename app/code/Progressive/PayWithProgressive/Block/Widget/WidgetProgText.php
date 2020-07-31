<?php

/**
 * Copyright (c) 2018, Prog Leasing, LLC
 * Licensed under the Open Software License version 3.0
 */

namespace Progressive\PayWithProgressive\Block\Widget;

use Magento\Framework\View\Element\Template;
use Magento\Widget\Block\BlockInterface;
use Progressive\PayWithProgressive\Helper\ConfigInfo;
use Progressive\PayWithProgressive\Helper\EcomSystem;

class WidgetProgText extends Template implements BlockInterface {
    protected $_template = "Progressive_PayWithProgressive::widget/ECOM_Banner_Text.phtml";
    protected $_system;
    protected $_config;
	protected $_data;
	
    public function __construct(
        Template\Context $context,
        EcomSystem $eComSystem,
        ConfigInfo $configInfo,
        $data = array()
    )
    {
        parent::__construct($context, $data);
        $this->setTemplate($this->_template);
        $this->_system = $eComSystem;
        $this->_config = $configInfo;
		$this->_data = $data;
    }

    /**
     * getClientToken()
     *
     * post https://progressivelp.com/eComApiBeta/v1/session) returns clientToken
     * If session/clientToken is null then an empty call to the session endpoint
     * is made to populate clientToken (or sessionId), store it in the session and
     * return it
     *
     * @return string|null
     *
     */
    public function getClientToken()
    {
        $clientToken = null;
        $query_data = array();

        $clientToken = $this->_system->getClientToken();
        if ($clientToken === null) {
            if ($this->_system->postSession($query_data) == 200) {
                $clientToken = $this->_system->getClientToken();
            }
        }
        return $clientToken;
    }

    public function getBannerText()
    {
        return $this->_data['banner_text']. " | LEARN MORE ";
    }

    public function getApiUrl()
    {
        return $this->_config->getApiUrl();
    }

}


