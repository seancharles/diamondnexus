<?php

namespace ForeverCompanies\Gifts\Helper;

use Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;

class Data extends AbstractHelper
{
    /**
     * @var CollectionFactory
     */
    protected $attributeSetCollectionFactory;

    /**
     * Data constructor.
     * @param Context $context
     * @param CollectionFactory $attributeSetCollectionFactory
     */
    public function __construct(Context $context, CollectionFactory $attributeSetCollectionFactory)
    {
        parent::__construct($context);
        $this->attributeSetCollectionFactory = $attributeSetCollectionFactory;
    }

    public function isEnabled()
    {
        return $this->scopeConfig->getValue('forevercompanies_gifts/general/active');
    }

    public function getAttributeSetId($name)
    {
        $set = $this->attributeSetCollectionFactory->create()->addFieldToFilter('attribute_set_name', $name);
        return $set->getFirstItem()->getData('attribute_set_id');
    }
}
