<?
    error_reporting(-1);

  
    require_once $_SERVER['HOME'].'magento/shell/dnl/encoding.php';
    require_once $_SERVER['HOME'].'magento/shell/dnl/hubspot-columns.php';
    $mage = Mage::app();
    $storeId = Mage::app()->getStore()->getId();
    $userModel = Mage::getModel('admin/user');
    $userModel->setUserId(0);
    Mage::getSingleton('admin/session')->setUser($userModel);
    $adapter = Mage::getSingleton('core/resource')->getConnection('core_read');

    $collection_size = 25000;
    $collection_total = Mage::getModel('customer/customer')->getCollection()
	->getSize();
    for ($count = 0; $count < $collection_total; $count += $collection_size) {
	    $collection = Mage::getModel('customer/customer')->getCollection()
		->setOrder('entity_id','DESC')
		->setPage($count, ($count + $collection_size));

            print "Generating ".$count." - ".($count + $collection_size)." out of ".$collection_total."\n";
	    $fpbase = fopen('var/export/hubspot/base_info_'.$count.'_'.($count + $collection_size).'_.csv', 'w');
	    fputcsv($fpbase, $basecolumns);
	    $fporder = fopen('var/export/hubspot/order_info_'.$count.'_'.($count + $collection_size).'_.csv', 'w');
	    fputcsv($fporder, $ordercolumns);
	    $fpcart = fopen('var/export/hubspot/cart_info_'.$count.'_'.($count + $collection_size).'_.csv', 'w');
	    fputcsv($fpcart, $cartcolumns);

	    foreach ($collection as $customer) {
		$customer_line = array();
		$customerData = Mage::getModel('customer/customer')->load($customer->getId());
		if ( preg_match('/emaildomain/', $customerData->getEmail()) || preg_match('/diamondnexus/', $customerData->getEmail()) || !(filter_var($customerData->getEmail(), FILTER_VALIDATE_EMAIL))) {
			continue;
		} 
		#print "Customer ID: ". $customerData->getId()."\n";
		$select = $adapter->select()
		    ->from($adapter->getTableName('enterprise_customersegment_customer'))
		    ->where('customer_id = ?', (int) $customer->getId());
		$customer_line['eyemagine_customer_id'] = $customerData->getId();
		switch($adapter->fetchAll($select)[0]['segment_id']) {
		    case '5':
			$customer_line['lifecyclestage'] = 'Customer';
			break;
		    case '4':
		    case '3':
		    default:
			$customer_line['lifecyclestage'] = 'Lead';
			#$customer_line['lifecyclestage'] = 'Subscriber';
			break;
		}
		#print "Customer segment: ".$customer_line['lifecyclestage']."\n";
		#print $customerData->getFirstname()."\n";
		$customer_line['firstname'] = $customerData->getFirstname();
		#print $customerData->getLastname()."\n";
		$customer_line['lastname'] = $customerData->getLastname();
		#print $customerData->getEmail()."\n";
		$customer_line['email'] = $customerData->getEmail();
		#print $customerData->getPhone()."\n";
		$customer_line['phone'] = $customerData->getPhone();
		#print $customerData->getStoreId()."\n";
		$customer_line['eyemagine_store_id'] = $customerData->getStoreId();
		#print $customerData->getWebsiteId()."\n";
		$customer_line['eyemagine_website_id'] = $customerData->getWebsiteId();
		#print $customerData->getGroupId()."\n";
		$customer_line['eyemagine_group_id'] = $customerData->getGroupId();
		#print $customerData->getIsActive()."\n";
		$customer_line['eyemagine_is_active'] = $customerData->getIsActive();
		#print $customerData->getCreatedAt()."\n";
		$group = Mage::getModel('customer/group')->load($customerData->getGroupId());
		#print $group->getCode()."\n";
		$customer_line['eyemagine_lifetime_customer_since'] = date('m/d/Y', Mage::getModel('core/date')->timestamp(strtotime($customerData->getCreatedAt())));
		$customer_line['eyemagine_customer_group'] = $group->getCode();
		$defaultBilling = $customerData->getDefaultBilling();
		$customer_line['eyemagine_default_billing_address'] = $customerData->getDefaultBilling();
		if ($defaultBilling) {
		    $billAddress = Mage::getModel('customer/address')->load($defaultBilling);
		    #print "Billing ID: ". $billAddress->getId()."\n";
		    #print $billAddress->getStreet()[0]."\n";
		    $customer_line['address'] = $billAddress->getStreet()[0];
		    #print $billAddress->getCity()."\n";
		    $customer_line['city'] = $billAddress->getCity();
		    #print $billAddress->getRegion()."\n";
		    $customer_line['state'] = $billAddress->getRegion();
		    #print $billAddress->getCountry()."\n";
		    $customer_line['country'] = $billAddress->getCountry();
		    #print $billAddress->getPostcode()."\n";
		    $customer_line['zip'] = $billAddress->getPostcode();
		}
		$defaultShipping = $customerData->getDefaultShipping();
		$customer_line['eyemagine_default_shipping_address'] = $defaultShipping;
		if ($defaultShipping) {
		    $shipAddress = Mage::getModel('customer/address')->load($defaultShipping);
		    #print "Shipping ID: ".$shipAddress->getId()."\n";
		}
		$lastorder = Mage::getResourceModel('sales/order_collection')
		    ->addFieldToFilter('customer_id', $customerData->getId())
		    ->addAttributeToSort('created_at', 'DESC')
		    ->setPageSize(1);

		foreach($lastorder as $last) {
		    $last_order_date = date('m/d/Y', Mage::getModel('core/date')->timestamp(strtotime($last->getCreatedAt())));
		    #print "Last Order ID: ".$last->getIncrementId()."\n";
		    $customer_line['eyemagine_last_order_increment_id'] = $last->getIncrementId();
		    $customer_line['eyemagine_order_increment_id'] = $last->getIncrementId();
		    #print "  Created: ".$last->getCreatedAt()."\n";
		    $customer_line['eyemagine_order_created_at'] = date('m/d/Y', Mage::getModel('core/date')->timestamp(strtotime($last->getCreatedAt())));
		    #print "  Currency: ".$last->getOrderCurrencyCode()."\n";
		    $customer_line['eyemagine_order_base_currency'] = $last->getOrderCurrencyCode();
		    #print "  Subtotal: ".$last->getSubtotal()."\n";
		    $customer_line['eyemagine_order_subtotal'] = $last->getSubtotal();
		    #print "  Discount: ".$last->getDiscount()."\n";
		    $customer_line['eyemagine_order_discount'] = $last->getDiscount();
		    #print "  Tax: ".$last->getTax()."\n";
		    $customer_line['eyemagine_order_tax'] = $last->getTax();
		    #print "  Shipping: ".$last->getShipping()."\n";
		    $customer_line['eyemagine_order_shipping'] = $last->getShipping();
		    #print "  Grand Total: ".$last->getGrandTotal()."\n";
		    $customer_line['eyemagine_order_grand_total'] = $last->getGrandTotal();
		    #print "  Coupon Code: ".$last->getCouponCode()."\n";
		    $customer_line['eyemagine_order_coupon_code'] = $last->getCouponCode();
		    $stop_at_three = 0;
		    foreach ($last->getAllItems() as $item) {
			if($stop_at_three >= 3) { break; }
			$product = Mage::getModel('catalog/product')->load($item->getProductId());
			#print "  Order Item ID: ".$item->getProductId()."\n";
			#print "    Order Item Name: ".$item->getName()."\n";
			$customer_line['eyemagine_order_item_product_name_'.($stop_at_three + 1)] = htmlspecialchars($item->getName());
			#print "    Order Item SKU: ".$item->getSku()."\n";
			$customer_line['eyemagine_order_item_product_sku_'.($stop_at_three + 1)] = $item->getSku();
			#print "    Order Item URL: ".$product->getUrlModel()->getUrl($product)."\n";
			$customer_line['eyemagine_order_item_product_url_'.($stop_at_three + 1)] = $product->getUrlModel()->getUrl($product);
			#print "    Order Item Img: ".$product->getMediaConfig()->getMediaUrl($product->getData('image'))."\n";
			$customer_line['eyemagine_order_item_product_img_'.($stop_at_three + 1)] = $product->getMediaConfig()->getMediaUrl($product->getData('image'));
			#print "    Order Item QTY: ".$item->getQtyOrdered()."\n";
			#print "    Order Item Cat: ".$item->getCategoryIds()."\n";
			$customer_line['eyemagine_order_item_product_category_name_'.($stop_at_three + 1)] = $item->getCategoryIds();
			$stop_at_three += 1;
		    }
		}

		$orders = Mage::getResourceModel('sales/order_collection')
		    ->addFieldToFilter('customer_id', $customerData->getId())
		    ->addAttributeToSort('created_at', 'ASC');

		$order_count = 0;
		$order_sum = 0;
		$item_count = 0;
		$item_sum = 0;
		$order_date = NULL;
		foreach($orders as $order) {
		    if ($order_date === NULL) { $order_date = date('m/d/Y', Mage::getModel('core/date')->timestamp(strtotime($order->getCreatedAt()))); }
		    #print "Order ID: ".$order->getIncrementId()."\n";
		    #print "  Grand Total: ".$order->getGrandTotal()."\n";
		    $order_sum += $order->getGrandTotal();
		    $order_count += 1;
		    foreach ($order->getAllItems() as $item) {
			#print "  Order Item ID: ".$item->getId()."\n";
			#print "    Price: ".$item->getPrice()."\n";
			$item_sum += $item->getPrice();
			$item_count += 1;
		    }
		}
		$customer_line['total_revenue'] = $order_sum;

		$quotes = Mage::getResourceModel('sales/quote_collection')
		    ->addFieldToFilter('customer_id', $customerData->getId())
		    ->setOrder('created_at','DESC')
		    ->setPageSize(1);

		foreach($quotes as $quote) {
		    #print "Cart ID: ".$quote->getId()."\n";
		    #print "Cart Created: ".$quote->getCreatedAt()."\n";
		    $customer_line['eyemagine_abandoned_created_at'] = date('m/d/Y', Mage::getModel('core/date')->timestamp(strtotime($quote->getCreatedAt())));
		    #print "Cart Currency: ".$quote->getQuoteCurrencyCode()."\n";
		    $customer_line['eyemagine_abandoned_base_currency'] = $quote->getQuoteCurrencyCode();
		    #print "Cart Subtotal: ".$quote->getSubtotal()."\n";
		    $customer_line['eyemagine_abandoned_subtotal'] = $quote->getSubtotal();
		    #print "Cart Grand total: ".$quote->getGrandTotal()."\n";
		    $customer_line['eyemagine_abandoned_grand_total'] = $quote->getGrandTotal();
		    #print "Cart Coupon Code: ".$quote->getCouponCode()."\n";
		    $customer_line['eyemagine_abandoned_coupon_code'] = $quote->getCouponCode();
		    if ($quote->getItemsCount() > 0) {
			$stop_at_three = 0;
			foreach ($quote->getAllVisibleItems() as $item) {
			    if($stop_at_three >= 3) { break; }
			    $product = Mage::getModel('catalog/product')->load($item->getProductId());
			    #print "  Cart Item ID: ".$item->getProductId()."\n";
			    #print "    Cart Item Name: ".$item->getName()."\n";
			    $customer_line['eyemagine_abandoned_item_product_name_'.($stop_at_three + 1)] = $item->getName();
			    #print "    Cart Item SKU: ".$item->getSku()."\n";
			    $customer_line['eyemagine_abandoned_item_product_sku_'.($stop_at_three + 1)] = $item->getSku();
			    #print "    Cart Item URL: ".$product->getUrlModel()->getUrl($product)."\n";
			    $customer_line['eyemagine_abandoned_item_product_url_'.($stop_at_three + 1)] = $product->getUrlModel()->getUrl($product);
			    #print "    Cart Item Img: ".$product->getMediaConfig()->getMediaUrl($product->getData('image'))."\n";
			    $customer_line['eyemagine_abandoned_item_product_img_'.($stop_at_three + 1)] = $product->getMediaConfig()->getMediaUrl($product->getData('image'));
			    #print "    Cart Item QTY: ".$item->getQty()."\n";
			    #print "    Cart Item Cat: UGH!\n";
			    $customer_line['eyemagine_abandoned_item_product_category_name_'.($stop_at_three + 1)] = $item->getCategoryIds();
			    $stop_at_three += 1;
			}
		    }
		}
		if ($order_date != NULL) {
		    #print "First Order Date: ".$order_date."\n";
		    $customer_line['eyemagine_lifetime_first_order_date'] = $order_date;
		    #print "Last Order Date: ".$last_order_date."\n";
		    $customer_line['eyemagine_lifetime_last_order_date'] = $last_order_date;
		    #print "Order Count: ".$order_count."\n";
		    $customer_line['eyemagine_lifetime_order_count'] = $order_count;
		    #print "Order Sum: ".$order_sum."\n";
		    $customer_line['eyemagine_lifetime_order_total_sum'] = $order_sum;
		    #print "Order Avg: ".($order_sum/$order_count)."\n";
		    $customer_line['eyemagine_lifetime_order_total_avg'] = ($order_sum/$order_count);
		    #print "Item Count: ".$item_count."\n";
		    #print "Item Sum: ".$item_sum."\n";
		    $customer_line['eyemagine_lifetime_qty_sum'] = $item_count;
		    #print "Item Qty Avg: ".($item_sum/$order_count)."\n";
		    $customer_line['eyemagine_lifetime_qty_avg'] = ($item_sum/$order_count);
		    #print "Item Avg: ".($item_sum/$item_count)."\n";
		    $customer_line['eyemagine_lifetime_item_price_avg'] = ($item_sum/$item_count);
		    $ordered_line = array();
		    foreach ($ordercolumns as $field) {
			$ordered_line[] = $customer_line[$field];
		    }
		    fputcsv($fporder, $ordered_line);
		}
		$ordered_line = array();
		foreach ($basecolumns as $field) {
		    $ordered_line[] = $customer_line[$field];
		}
		fputcsv($fpbase, $ordered_line);
		$ordered_line = array();
		foreach ($cartcolumns as $field) {
		    $ordered_line[] = $customer_line[$field];
		}
		fputcsv($fpcart, $ordered_line);
	    }
	    fclose($fpbase);
	    fclose($fporder);
	    fclose($fpcart);
	}
?>
