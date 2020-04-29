<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\NegotiableQuote\Test\Block;

use Magento\Mtf\Block\Form;
use Magento\Mtf\Client\Element\SimpleElement;

/**
 * Class RequestQuotePopup
 * Request new quote popup on Storefront
 */
class RequestQuotePopup extends Form
{
    /**
     * Save button selector
     *
     * @var string
     */
    protected $saveSelector = '.action.save';

    /**
     * Fill form
     *
     * @param array $data
     * @param SimpleElement|null $element
     * @return $this
     * @throws \Exception
     */
    public function fillForm(array $data, SimpleElement $element = null)
    {
        $fields = isset($data['fields']) ? $data['fields'] : $data;
        $mapping = $this->dataMapping($fields);
        $this->_fill($mapping, $element);

        return $this;
    }

    /**
     * Submit a qoute
     *
     * @return $this
     */
    public function submitQuote()
    {
        $this->_rootElement->find($this->saveSelector)->click();
        return $this;
    }
}
