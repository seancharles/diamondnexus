<?php

namespace ForeverCompanies\Gifts\Helper;

use Magento\Catalog\Model\Product;
use Magento\Eav\Model\Config;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Serialize\Serializer\Serialize;

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
     * @var Serialize
     */
    protected $serialize;

    /**
     * Data constructor.
     * @param Context $context
     * @param CollectionFactory $attributeSetCollectionFactory
     * @param Serialize $serialize
     * @param Config $eav
     */
    public function __construct(
        Context $context,
        CollectionFactory $attributeSetCollectionFactory,
        Serialize $serialize,
        Config $eav
    )
    {
        parent::__construct($context);
        $this->eav = $eav;
        $this->serialize = $serialize;
        $this->attributeSetCollectionFactory = $attributeSetCollectionFactory;
    }

    /**
     * @return bool
     */
    public function isEnabledPurchase()
    {
        return $this->getPurchaseConfig('active');
    }

    /**
     * @return bool
     */
    public function isEnabledFreeGift()
    {
        return $this->getGiftRules('active');
    }

    /**
     * @return string
     */
    public function isEnabledExpiredTime()
    {
        return $this->getGiftRules('expired');
    }

    /**
     * @return int
     */
    public function getExpiredTime()
    {
        return (int)$this->getGiftRules('time');
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
        return $this->getPurchaseConfig('product_id');
    }

    /**
     * @return string
     */
    public function getAmountForGift()
    {
        return $this->getPurchaseConfig('total');
    }

    /**
     * @return string
     */
    public function getGiftMessage()
    {
        return $this->getPurchaseConfig('message');
    }

    /**
     * @return string
     */
    public function getGiftLink()
    {
        return $this->getPurchaseConfig('link');
    }

    /**
     * @return array
     */
    public function getRules()
    {
        return $this->serialize->unserialize($this->getGiftRules('rules'));
    }

    /**
     * @param string $config
     * @return string
     */
    protected function getGiftRules(string $config)
    {
        return $this->scopeConfig->getValue('forevercompanies_gifts/free_gift_rules/' . $config);
    }

    /**
     * @param string $config
     * @return string
     */
    protected function getPurchaseConfig(string $config)
    {
        return $this->scopeConfig->getValue('forevercompanies_gifts/purchase/' . $config);
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
