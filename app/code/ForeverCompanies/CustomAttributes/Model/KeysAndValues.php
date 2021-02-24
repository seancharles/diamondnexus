<?php

namespace ForeverCompanies\CustomAttributes\Model;

use ForeverCompanies\CustomAttributes\Api\KeysAndValuesInterface;
use Magento\Catalog\Model\Product;
use Magento\Eav\Model\Config;
use Magento\Framework\Exception\LocalizedException;

class KeysAndValues implements KeysAndValuesInterface
{
    /**
     * @var Config
     */
    protected $eavConfig;

    public function __construct(Config $eavConfig)
    {
        $this->eavConfig = $eavConfig;
    }

    /**
     * @inheritDoc
     * @throws LocalizedException
     */
    public function getKeysAndValues(string $attribute)
    {
        $attributeSource = $this->eavConfig->getAttribute(Product::ENTITY, $attribute)->getSource();
        return $attributeSource->getAllOptions();
    }
}
