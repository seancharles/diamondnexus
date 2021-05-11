<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 *
 * Example Usage:
 *
 *   $this->mailHelper->addTo('pbaum83@gmail.com');
 *   $this->mailHelper->setSubject('hello world subject');
 *   $this->mailHelper->setBody('hello world body');
 *   $this->mailHelper->send();
 *
 */
declare(strict_types=1);

namespace ForeverCompanies\Smtp\Helper;

use Magento\Backend\Model\Auth\Session;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\App\Config\ScopeConfigInterface;

class Mail extends AbstractHelper
{
    const HTML_TEMPLATE = 'dynamic_html_email';
    const PLAINTEXT_TEMPLATE = 'dynamic_plaintext_email';
    
    const XML_PATH_FROM_GENERAL_EMAIL = 'trans_email/ident_general/email';
    const XML_PATH_FROM_GENERAL_NAME = 'trans_email/ident_general/name';
    
    // trans_email/ident_sales/email
    // trans_email/ident_support/email
    
    /**
     * @var Session
     */
    protected $session;
    
    /**
     * @var TransportBuilder
     */
    protected $transportBuilder;
    
    protected $isHtml = false;
    protected $template; // path to template file not template id
    
    protected $subject;
    protected $body;
    protected $to;
    protected $from;
    protected $store;
    

    /**
     * SalesPerson constructor.
     * @param Context $context
     * @param Session $session
     * @param TransportBuilder $transportBuilder
     */
    public function __construct(
        Context $context,
        Session $session,
        TransportBuilder $transportBuilder,
        ScopeConfigInterface $scopeConfig
    ){
        $this->session = $session;
        $this->transportBuilder = $transportBuilder;
        $this->scopeConfig = $scopeConfig;
        
        parent::__construct($context);
    }
    
    public function setFrom($from = false) {
        if($from !== false && is_array($from) === true) {
            $this->from = $from;
        }
    }
    
    public function addTo($to = false) {
        if($to !== false) {
            $this->to[] = $to;
        }
    }

    public function setSubject($subject = false) {
        if($subject !== false) {
            $this->subject = $subject;
        }
    }
    
    public function setTemplate($template = false) {
        if($template !== false) {
            $this->template = $template;
        }
    }
    
    public function setBody($body = false) {
        if($body !== false) {
            $this->body = $body;
        }
    }
    
    public function setIsHtml($isHtml = false) {
        if($isHtml !== false) {
            $this->isHtml = $isHtml;
        }
    }
    
    public function setStore($store = false) {
        if($store !== false) {
            $this->store = $store;
        }
    }

    /**
     * @param $id
     * @param $attribute
     * @return string
     */
    public function send()
    {
        if(is_array($this->to) !== true) {
            return;
        }

        if($this->store != null) {
            $storeScope = $this->store;
        } else {
            // default to current scope
            $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        }
        
        if($this->from === false || is_array($this->from) === false) {
            // default email sender
            $this->from = [
                'name' => $this->scopeConfig->getValue(self::XML_PATH_FROM_GENERAL_NAME, $storeScope),
                'email' => $this->scopeConfig->getValue(self::XML_PATH_FROM_GENERAL_EMAIL, $storeScope)
            ];
        }
        
        if($this->template != null) {
            $templateIdentifier = $this->template;
        } else {
            // default to plain text emails
            $templateIdentifier = ($this->isHtml == true) ? self::HTML_TEMPLATE : self::PLAINTEXT_TEMPLATE;
        }

        $postObject = new \Magento\Framework\DataObject();
        $postObject->setData([
            'body' => $this->body,
            'subject' => $this->subject
        ]);

        $transport = $this->transportBuilder
            ->setTemplateIdentifier($templateIdentifier)
            ->setTemplateOptions([
                'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                'store' => \Magento\Store\Model\Store::DEFAULT_STORE_ID]
            )
            ->setTemplateVars(['data' => $postObject])
            ->setFrom($this->from)
            ->addTo($this->to)
            ->getTransport();
            
        $transport->sendMessage();
    }

    /**
     * @return bool
     */
    public function isAllowed()
    {
        return $this->session->isAllowed('ForeverCompanies_Smtp::sendemail');
    }
}
