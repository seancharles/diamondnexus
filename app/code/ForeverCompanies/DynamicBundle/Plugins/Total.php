<?php

namespace ForeverCompanies\DynamicBundle\Plugins;

use Magento\SalesRule\Model\Quote\Discount as Source;

class Total
{
    public function afterCollect(Source $subject,
		$result,
		\Magento\Quote\Model\Quote $quote,
		\Magento\Quote\Model\ShippingAssignment $shippingAssignment,
		\Magento\Quote\Model\Quote\Address\Total $total
	)
    {
        $items = $shippingAssignment->getItems();
        if (!count($items)) {
            return $subject;
        }
        $address = $shippingAssignment->getShipping()->getAddress();
        $total->setDiscountDescription(implode(",", $address->getFullDescription()));
		
		mail('paul.baum@forevercompanies.com','around collect', print_r(get_class_methods($total)) );
		
        return $result;
    }
}