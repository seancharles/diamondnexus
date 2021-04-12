<?php
namespace ForeverCompanies\CustomLinked\Model\ProductLink\CollectionProvider;
class Customlinked {
    public function getLinkedProducts($product) {
        return $product->getCustomlinkedProducts();
    }
}
