<?php

namespace ForeverCompanies\CustomApi\Plugin\Ui\DataProvider\Product\Modifier;

class Attributes
{
    /**
     * Get Gift Wrapping
     *
     * @param \Magento\Catalog\Ui\DataProvider\Product\Modifier\Attributes $subject
     * @param array $data
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterModifyData(
        \Magento\Catalog\Ui\DataProvider\Product\Modifier\Attributes $subject,
        array $data
    ) {
        foreach ($data['items'] as &$item) {
            $item['name'] = str_replace("&#039;", "'", $item['name']);
            $item['name'] = str_replace("&amp;", "'", $item['name']);
        }
        return $data;
    }
}
