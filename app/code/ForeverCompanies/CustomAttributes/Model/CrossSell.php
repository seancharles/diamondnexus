<?php

namespace ForeverCompanies\CustomAttributes\Model;

use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\Model\AbstractModel;

class CrossSell extends AbstractModel implements IdentityInterface
{
    const CACHE_TAG = 'forevercompanies_customattributes_crosssell';

    protected $_cacheTag = self::CACHE_TAG;

    protected $_eventPrefix = self::CACHE_TAG;

    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    public function getDefaultValues()
    {
        return [];
    }

    protected function _construct()
    {
        $this->_init(ResourceModel\CrossSell::class);
    }

}
