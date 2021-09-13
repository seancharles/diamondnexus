<?php

namespace ForeverCompanies\CustomApi\Plugin;

use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Sales\Api\OrderItemRepositoryInterface;
use Magento\Sales\Api\Data\OrderItemSearchResultInterface;

class OrderItemSku
{
    /**
     *
     * @param OrderItemRepositoryInterface $subject
     * @param OrderItemInterface $orderItem
     *
     * @return OrderItemInterface
     */
    public function afterGet(
        OrderItemRepositoryInterface $subject,
        OrderItemInterface $orderItem
    ) {
        $orderItem->setSku($this->transformFishbowlItemSku($orderItem));

        return $orderItem;
    }

    /**
     *
     * @param OrderItemRepositoryInterface $subject
     * @param OrderItemSearchResultInterface $searchResult
     *
     * @return OrderItemSearchResultInterface
     */
    public function afterGetList(OrderItemRepositoryInterface $subject, OrderItemSearchResultInterface $searchResult)
    {
        $orderItems = $searchResult->getItems();

        foreach ($orderItems as &$item) {
            $item->setSku($this->transformFishbowlItemSku($item));
        }

        return $searchResult;
    }

    protected function transformFishbowlItemSku($item)  {
        $sku = str_replace("-", "", $item->getSku());

        $prefix = strtoupper(substr($sku,0,4));
        $metal = strtoupper(substr($sku,24,2));
        $line = strtoupper(substr($sku,4,2));

        if(
            ( $prefix == 'MRWB' ) ||
            ( $prefix == 'MRFS' ) ||
            ( $prefix == 'LREN' &&  $line == 'RS') ||
            ( $prefix == 'LREB' ) ||
            ( $prefix == 'MRTT' ) ||
            ( $prefix == 'MRWB' ) ||
            ( $metal == 'CO' ) ||
            ( $metal == 'TT' ) ||
            ( $prefix == 'MRTN') ||
            ( $prefix == 'LRRH' && $metal == 'LP' )
        ) {
            $sku = substr($sku,0,32);
        } else {
            $sku = substr($sku,0,28);
        }

        return $sku;
    }
}
