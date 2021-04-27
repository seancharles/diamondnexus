<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace ForeverCompanies\Checkout\Block\Cart\Item\Renderer\Actions;

use Magento\Checkout\Helper\Cart;
use Magento\Framework\View\Element\Template;

/**
 * @api
 * @since 100.0.2
 */
class Remove extends \Magento\Checkout\Block\Cart\Item\Renderer\Actions\Remove
{
    /**
     * @var Cart
     */
    protected $cartHelper;

    /**
     * @param Template\Context $context
     * @param Cart $cartHelper
     * @param array $data
     * @codeCoverageIgnore
     */
    public function __construct(
        Template\Context $context,
        Cart $cartHelper,
        \Magento\Framework\Data\Form\FormKey $formKey,
        array $data = []
    ) {
        $this->cartHelper = $cartHelper;
        $this->formKey = $formKey;
        parent::__construct($context, $cartHelper, $data);
    }

    
    /**
     * Get rebuild item POST JSON
     *
     * @return string
     * @codeCoverageIgnore
     */
    public function getRebuildPostJson()
    {
        $item = $this->getItem();
        
        $url = $this->getUrl('fcprofile/ringbuild/rebuild');

        $data = [
            'itemid' => $item->getId(),
            'setid' => $item->getSetId(),
            'form_key' => $this->formKey->getFormKey()
        ];
            
        return json_encode(['action' => $url, 'data' => $data]);
    }
    
    public function isRingBuilderSet()
    {
        if($this->getItem()->getSetId() > 0) {
            return true;
        } else {
            return false;
        }
    }
}
