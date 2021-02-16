<?php

namespace ForeverCompanies\CustomApi\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

interface CustomOptionInterface extends ExtensibleDataInterface
{
    const OPTION_ID = 'option_id';
    const OPTION_VALUE = 'option_value';
    const OPTION_TITLE = 'option_title';
    const OPTION_VALUE_TITLE = 'option_value_title';

    /**
     * @return string
     */
    public function getOptionId();

    /**
     * @param string $optionId
     * @return $this
     */
    public function setOptionId($optionId);

    /**
     * @return string
     */
    public function getOptionValue();

    /**
     * @param string $optionValue
     * @return $this
     */
    public function setOptionValue($optionValue);

    /**
     * @return string
     */
    public function getOptionTitle();

    /**
     * @param string $optionTitle
     * @return $this
     */
    public function setOptionTitle($optionTitle);

    /**
     * @return string
     */
    public function getOptionValueTitle();

    /**
     * @param string $optionValueTitle
     * @return $this
     */
    public function setOptionValueTitle($optionValueTitle);
}
