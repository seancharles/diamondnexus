<?php

namespace ForeverCompanies\Checkout\Plugins\Quote;

class Item
{
    public function afterRepresentProduct(\Magento\Quote\Model\Quote\Item $subject, $result)
    {
        if (
            $subject->getProduct()->getAttributeSetId() == 18 ||
            $subject->getProduct()->getAttributeSetId() == 31 ||
            $subject->getProduct()->getAttributeSetId() == 32
        ) {
             $result = false;
        }
    }
}
