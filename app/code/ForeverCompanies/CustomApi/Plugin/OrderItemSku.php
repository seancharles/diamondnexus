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
        $sku = str_replace("-", "", $orderItem->getSku());

        $prefix = strtoupper(substr($sku,0,4));
        $metal = strtoupper(substr($sku,24,2));
        $color = strtoupper(substr($sku,15,2));
        $line = strtoupper(substr($sku,4,2));

        if(
            ( $prefix == 'MRTN' ) ||
            ( $prefix == 'MRTT' ) ||
            ( $prefix == 'MRWB' ) ||
            ( $prefix == 'LRRH' ) ||
            ( $prefix == 'LRWB' ) ||
            ( $metal == 'CO' ) ||
            ( $metal == 'BZ' ) ||
            ( $metal == 'DM' ) ||
            ( $metal == 'TT' ) ||
            ( $prefix == 'LRRH' && $color == 'TZ') ||
            ( $prefix == 'LRRH' && $metal == 'LP') ||
            ( $prefix == 'LREB') ||
            ( $prefix == 'LREN') ||
            ( $line == 'RS')
        ) {
            $sku = substr($sku,0,28);
        } else {
            $sku = substr($sku,0,28);
        }

        $orderItem->setSku($sku);

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
            $sku = str_replace("-", "", $item->getSku());

            $prefix = strtoupper(substr($sku,0,4));
            $metal = strtoupper(substr($sku,24,2));
            $color = strtoupper(substr($sku,15,2));
            $line = strtoupper(substr($sku,4,2));

            if(
                ( $prefix == 'MRTN' ) ||
                ( $prefix == 'MRTT' ) ||
                ( $prefix == 'MRWB' ) ||
                ( $prefix == 'LRRH' ) ||
                ( $prefix == 'LRWB' ) ||
                ( $metal == 'CO' ) ||
                ( $metal == 'BZ' ) ||
                ( $metal == 'DM' ) ||
                ( $metal == 'TT' ) ||
                ( $prefix == 'LRRH' && $color == 'TZ') ||
                ( $prefix == 'LRRH' && $metal == 'LP') ||
                ( $prefix == 'LREB') ||
                ( $prefix == 'LREN') ||
                ( $line == 'RS')
            ) {
                $sku = substr($sku,0,28);
            } else {
                $sku = substr($sku,0,28);
            }

            $item->setSku($sku);
        }

        return $searchResult;
    }
}
