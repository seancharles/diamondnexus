<?php
namespace ForeverCompanies\CustomAttributes\Model\ResourceModel\Product;

use Magento\Store\Model\Store;

class Gallery extends \Magento\Catalog\Model\ResourceModel\Product\Gallery
{
  public function createBatchBaseSelect($storeId, $attributeId) {
    $linkField = $this->metadata->getLinkField();

    $positionCheckSql = $this->getConnection()->getCheckSql(
      'value.position IS NULL',
      'default_value.position',
      'value.position'
    );

    $mainTableAlias = $this->getMainTableAlias();

    $select = $this->getConnection()->select()->from(
      [$mainTableAlias => $this->getMainTable()],
      [
        'value_id',
        'file' => 'value',
        'media_type',
      ]
    )->joinInner(
      ['entity' => $this->getTable(self::GALLERY_VALUE_TO_ENTITY_TABLE)],
      $mainTableAlias . '.value_id = entity.value_id',
      [$linkField]
    )->joinLeft(
      ['value' => $this->getTable(self::GALLERY_VALUE_TABLE)],
      implode(
        ' AND ',
        [
          $mainTableAlias . '.value_id = value.value_id',
          $this->getConnection()->quoteInto('value.store_id = ?', (int) $storeId),
          'value.' . $linkField . ' = entity.' . $linkField,
        ]
      ),
      [
        'label',
        'position',
        'disabled',
        'catalog_product_option_type_id',
        'catalog_product_bundle_selection_id',
        'tags',
        'ui_role',
        'matching_band_product',
        'metal_type'
      ]
    )->joinLeft(
      ['default_value' => $this->getTable(self::GALLERY_VALUE_TABLE)],
      implode(
        ' AND ',
        [
          $mainTableAlias . '.value_id = default_value.value_id',
          $this->getConnection()->quoteInto('default_value.store_id = ?', Store::DEFAULT_STORE_ID),
          'default_value.' . $linkField . ' = entity.' . $linkField,
        ]
      ),
      [
        'label_default' => 'label',
        'position_default' => 'position',
        'disabled_default' => 'disabled',
        'catalog_product_option_type_id',
        'catalog_product_bundle_selection_id',
        'tags',
        'ui_role',
        'matching_band_product',
        'metal_type'
      ]
    )->where(
      $mainTableAlias . '.attribute_id = ?',
      $attributeId
    )->where(
      $mainTableAlias . '.disabled = 0'
    )->order(
      $positionCheckSql . ' ' . \Magento\Framework\DB\Select::SQL_ASC
    );

    return $select;
  }
}