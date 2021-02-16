<?php

namespace ForeverCompanies\CustomApi\Model;

use ForeverCompanies\CustomApi\Api\Data\CustomOptionInterface;
use ForeverCompanies\CustomApi\Api\Data\ExtSalesOrderUpdateInterface;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\AbstractModel;

class CustomOption extends AbstractModel implements IdentityInterface, CustomOptionInterface
{
    const CACHE_TAG = 'forevercompanies_customapi_customoption';

    /**
     * @var string
     */
    protected $_cacheTag = self::CACHE_TAG;

    /**
     * @var string
     */
    protected $_eventPrefix = self::CACHE_TAG;

    /**
     * @return string[]
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    /**
     * @return array
     */
    public function getDefaultValues()
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function getOptionId()
    {
        return $this->getData(self::OPTION_ID);
    }

    /**
     * @inheritDoc
     */
    public function setOptionId($optionId)
    {
        return $this->setData(self::OPTION_ID, $optionId);
    }

    /**
     * @inheritDoc
     */
    public function getOptionValue()
    {
        return $this->getData(self::OPTION_VALUE);
    }

    /**
     * @inheritDoc
     */
    public function setOptionValue($optionValue)
    {
        return $this->setData(self::OPTION_VALUE, $optionValue);
    }

    /**
     * @inheritDoc
     */
    public function getOptionTitle()
    {
        return $this->getData(self::OPTION_TITLE);
    }

    /**
     * @inheritDoc
     */
    public function setOptionTitle($optionTitle)
    {
        return $this->setData(self::OPTION_TITLE, $optionTitle);
    }

    /**
     * @inheritDoc
     */
    public function getOptionValueTitle()
    {
        return $this->getData(self::OPTION_VALUE_TITLE);
    }

    /**
     * @inheritDoc
     */
    public function setOptionValueTitle($optionValueTitle)
    {
        return $this->setData(self::OPTION_VALUE_TITLE, $optionValueTitle);
    }
}
