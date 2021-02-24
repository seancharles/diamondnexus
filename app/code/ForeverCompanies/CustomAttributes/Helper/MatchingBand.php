<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace ForeverCompanies\CustomAttributes\Helper;

use ForeverCompanies\CustomAttributes\Model\ResourceModel\CrossSell;
use Magento\Catalog\Model\Product;
use Magento\Eav\Model\Config;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\LocalizedException;

class MatchingBand extends AbstractHelper
{
    /**
     * @var Config
     */
    protected $eavConfig;

    /**
     * @var CrossSell
     */
    protected $crossSellResource;

    /**
     * @var string[]
     */
    protected $matchingBandWhere = [
        'entity_varchar.value LIKE \'%Wedding Band%\'',
        'entity_varchar.value LIKE \'%Matching Band%\'',
        'entity_varchar.value = \'Miami\'',
        'entity_varchar.value = \'San Francisco\''
    ];

    /**
     * Curl constructor.
     * @param Context $context
     * @param Config $eavConfig
     * @param CrossSell $crossSellResource
     */
    public function __construct(
        Context $context,
        Config $eavConfig,
        CrossSell $crossSellResource
    ) {
        parent::__construct($context);
        $this->eavConfig = $eavConfig;
        $this->crossSellResource = $crossSellResource;
    }

    /**
     * @param int $entityId
     * @return array|void
     */
    public function getMatchingBands(int $entityId)
    {
        try {
            $attributeId = $this->eavConfig->getAttribute(Product::ENTITY, 'name')->getAttributeId();
            $select = $this->crossSellResource->getCrossSellSelect();
            $select->where('main_table.parent_id = ?', $entityId)
                ->where('entity_varchar.attribute_id = ?', $attributeId)
                ->where(implode(' OR ', $this->matchingBandWhere))
                ->columns(['main_table.product_id', 'entity_varchar.value', 'entity.sku']);
            return $select->getConnection()->fetchAll($select);
        } catch (LocalizedException $e) {
            $this->_logger->critical('Can\'t get matching bands for product ID = ' . $entityId);
        }
    }

    /**
     * @param int $entityId
     * @return array|void
     */
    public function getEnhancers(int $entityId)
    {
        try {
            $attributeId = $this->eavConfig->getAttribute(Product::ENTITY, 'name')->getAttributeId();
            $select = $this->crossSellResource->getCrossSellSelect();
            $select->where('main_table.parent_id = ?', $entityId)
                ->where('entity_varchar.attribute_id = ?', $attributeId)
                ->columns(['main_table.product_id', 'entity_varchar.value', 'entity.sku']);
            return $select->getConnection()->fetchAll($select);
        } catch (LocalizedException $e) {
            $this->_logger->critical('Can\'t get enhancers for product ID = ' . $entityId);
        }
    }
}
