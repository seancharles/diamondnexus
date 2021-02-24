<?php

namespace ForeverCompanies\Gifts\Helper;

use Magento\Catalog\Model\Product;
use Magento\Eav\Model\Config;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\LocalizedException;

class Data extends AbstractHelper
{
    /**
     * @var CollectionFactory
     */
    protected $attributeSetCollectionFactory;

    /**
     * @var Config
     */
    protected $eav;

    /**
     * Data constructor.
     * @param Context $context
     * @param CollectionFactory $attributeSetCollectionFactory
     * @param Config $eav
     */
    public function __construct(
        Context $context,
        CollectionFactory $attributeSetCollectionFactory,
        Config $eav
)
    {
        parent::__construct($context);
        $this->eav = $eav;
        $this->attributeSetCollectionFactory = $attributeSetCollectionFactory;
    }

    public function isEnabled()
    {
        return $this->scopeConfig->getValue('forevercompanies_gifts/general/active');
    }

    /**
     * @param $name
     * @return string|null
     */
    public function getAttributeSetId($name)
    {
        $set = $this->attributeSetCollectionFactory->create()->addFieldToFilter('attribute_set_name', $name);
        return $set->getFirstItem()->getData('attribute_set_id');
    }

    /**
     * @return string
     */
    public function getGiftProductId()
    {
        return $this->scopeConfig->getValue('forevercompanies_gifts/purchase/product_id');
    }

    /**
     * @return string
     */
    public function getAmountForGift()
    {
        return $this->scopeConfig->getValue('forevercompanies_gifts/purchase/total');
    }

    /**
     * @return string
     */
    public function getGiftMessage()
    {
        return $this->scopeConfig->getValue('forevercompanies_gifts/purchase/message');
    }

    /**
     * @return string
     */
    public function getGiftLink()
    {
        return $this->scopeConfig->getValue('forevercompanies_gifts/purchase/link');
    }

    /**
     * @return array
     */
    public function getRules()
    {
        return $this->scopeConfig->getValue('forevercompanies_gifts/free_gift_rules/rules');
    }

    /**
     * @param $attribute
     * @param $id
     * @return bool|string
     */
    public function getValue($attribute, $id)
    {
        try {
            $source = $this->eav->getAttribute(Product::ENTITY, $attribute)->getSource();
            return $source->getOptionText($id);
        } catch (LocalizedException $e) {
            return false;
        }
    }
}
