<?php
require_once $_SERVER['HOME'].'magento/shell/dnl/google_api/BaseFeed.php';

class ProductsFeed extends BaseFeed {
  // These constants define the identifiers for all of our example products
  // The products will be sold online
  const CHANNEL = 'online';
  // The product details are provided in English
  const CONTENT_LANGUAGE = 'en';
  // The products are sold in the United States
  const TARGET_COUNTRY = 'US';

  // This constant defines how many example products to create in a batch
  const BATCH_SIZE = 250;

  public function run() {
    $this->listProducts();
  }

  public function getMerchantAccount() {
    $this->account = $this->service->accounts->get($this->merchant_id, $this->merchant_id);
    return $this->account;
  }

  public function updateMerchant() {
    $this->account = $this->service->accounts->get($this->merchant_id, $this->merchant_id);
    $this->account->setReviewsUrl($this->getWebUrl().'var/export/reviews/full/'. $this->getStoreId() .'/reviews.xml');
    $this->service->accounts->update($this->merchant_id, $this->merchant_id, $this->account);
    return $this->account;
  }

  public function listDatafeedstatuses($merchantId, $optParams = array())
  {
    return $this->service->datafeedstatuses->listDatafeedstatuses($this->merchant_id);
  }

  public function insertProduct(Google_Service_Content_Product $product) {
    $response = $this->service->products->insert($this->merchant_id, $product);
    $warnings = $response->getWarnings();
    foreach($warnings as $warning) {
      printf(" %s [%s] %s\n", $product->getOfferId, $warning->getReason(), $warning->getMessage());
    }
  }

  public function getProduct($offer_id) {
    $product_id = $this->buildProductId($offer_id);
    $product = $this->service->products->get($this->merchant_id, $product_id);
    return $product;
  }

  public function updateProduct(Google_Service_Content_Product $product) {
    $response = $this->service->products->insert($this->merchant_id, $product);

    // We should get one fewer warning now
    $warnings = $response->getWarnings();
    foreach($warnings as $warning) {
      printf(" %s [%s] %s\n", $product->getOfferId, $warning->getReason(), $warning->getMessage());
    }
  }

  public function deleteProduct($offer_id) {
    $product_id = $this->buildProductId($offer_id);
    // The response for a successful delete is empty
    $this->service->products->delete($this->merchant_id, $product_id);
  }

  public function insertProductBatch($products) {
    $entries = array();

    foreach ($products as $key => $product) {
      $entry = new Google_Service_Content_ProductsCustomBatchRequestEntry();
      $entry->setMethod('insert');
      $entry->setBatchId($key);
      $entry->setProduct($product);
      $entry->setMerchantId($this->merchant_id);

      $entries[] = $entry;
    }

    $batch_request = new Google_Service_Content_ProductsCustomBatchRequest();
    $batch_request->setEntries($entries);

    $batch_response = $this->service->products->custombatch($batch_request);

    printf("Inserted %d products.\n", count($batch_response->entries));

  }

  public function listProducts() {
    // We set the maximum number of results to be lower than the number of
    // products that we inserted, to demonstrate paging.
    $parameters = array('maxResults' => self::BATCH_SIZE - 1);
    $count = 0;
    $results = array();
    while ($products = $this->service->products->listProducts($this->merchant_id, $parameters)) {
        foreach ($products->getResources() as $product) {
          $results[] = $product->getOfferId();
        }
        // If the result has a nextPageToken property then there are more pages
        // available to fetch
        if (!$products->getNextPageToken()) {
          break;
        }
        // You can fetch the next page of results by setting the pageToken
        // parameter with the value of nextPageToken from the previous result.
        $parameters['pageToken'] = $products->nextPageToken;
    }
    return $results;
  }

  public function deleteProductBatch($offer_ids) {
    $entries = array();

    foreach ($offer_ids as $key => $offer_id) {
      $entry = new Google_Service_Content_ProductsCustomBatchRequestEntry();
      $entry->setMethod('delete');
      $entry->setBatchId($key);
      $entry->setProductId($this->buildProductId($offer_id));
      $entry->setMerchantId($this->merchant_id);

      $entries[] = $entry;
    }

    $batch_request = new Google_Service_Content_ProductsCustomBatchRequest();
    $batch_request->setEntries($entries);

    $batch_responses = $this->service->products->custombatch($batch_request);
    $errors = 0;
    foreach ($batch_responses->entries as $entry) {
      if ($entry->getErrors()) {
        $errors++;
      }
    }
    print "Requested delete of batch inserted test products\n";
    printf("There were %d errors\n", $errors);
  }

  private function buildProductId($offer_id) {
    return sprintf('%s:%s:%s:%s', self::CHANNEL, self::CONTENT_LANGUAGE,
      self::TARGET_COUNTRY, $offer_id);
  }

  public function createProducts($offer_ids) {
    $products = array();

    foreach ($offer_ids as $offer_id) {
      $products[] = $this->createProduct($offer_id);
    }

    return $products;
  }

  public function createShopProduct($offer_id) {
    $product = new Google_Service_Content_Product();
    $product->setAdwordsGrouping($offer_id[0]);
    $product->setAgeGroup('adult');
    $product->setBrand($this->getWebsiteName());
    $product->setCondition('new');
    $product->setDescription($offer_id[4]);
    $product->setGender($offer_id[5]);
    $product->setImageLink($offer_id[6]);
    $product->setLink($offer_id[7]);
    $price = new Google_Service_Content_Price();
    $price->setValue($offer_id[8]);
    $price->setCurrency('USD');
    $product->setPrice($price);
    $sprice = new Google_Service_Content_Price();
    if($offer_id[21] != $offer_id[8] && $offer_id[21] > 0) {
	        $sprice->setValue($offer_id[21]);
        	$sprice->setCurrency('USD');
	        $product->setPrice($sprice);
	        $product->setSalePrice($sprice);
    		$product->setCustomLabel1($offer_id[25]);
    	if ((strtotime("+1 year") > strtotime($offer_id[23])) && (strtotime($offer_id[22]) < strtotime($offer_id[23]))) {
        	$product->setSalePriceEffectiveDate($offer_id[22]."/".$offer_id[23]);
	}
    }
    $product->setMpn($offer_id[9]);
    $shipping_weight = new Google_Service_Content_ProductShippingWeight();
    $shipping_weight->setValue(1);
    $shipping_weight->setUnit('pounds');
    $product->setShippingWeight($shipping_weight);
    $product->setColor($offer_id[11]);
    $product->setMaterial($offer_id[12]);
    //$product->setSizes($offer_id[13]);
    $product->setTitle($offer_id[14]);
    $product->setGoogleProductCategory($offer_id[15]);
    //$product->setOfferId($this->buildProductId($offer_id[16]));
    $product->setOfferId($offer_id[16]);
    $product->setAvailability('in stock');
    $product->setItemGroupId($offer_id[18]);
    $product->setProductType($offer_id[19]);
    $product->setContentLanguage(self::CONTENT_LANGUAGE);
    $product->setTargetCountry(self::TARGET_COUNTRY);
    $product->setChannel(self::CHANNEL);
    $product->setDestinations(array(array('destinationName' => 'Shopping', 'intention' => 'required')));
    if(!empty($offer_id[26])) {
	    $product->setCustomLabel2($offer_id[26]);
    }
    if(!empty($offer_id[27])) {
	    $product->setCustomLabel3($offer_id[27]);
    }

//    $shipping_price = new Google_Service_Content_Price();
//    $shipping_price->setValue();
//    $shipping_price->setCurrency();

//    $shipping = new Google_Service_Content_ProductShipping();
//    $shipping->setPrice($shipping_price);
//    $shipping->setCountry('US');
//    $shipping->setService('Standard shipping');

//    $product->setShipping(array($shipping));

    return $product;
  }

  public function createAdsProduct($offer_id) {
    $product = new Google_Service_Content_Product();
    $product->setAdwordsGrouping($offer_id[0]);
    $product->setAgeGroup('adult');
    $product->setBrand($this->getWebsiteName());
    $product->setCondition('new');
    $product->setDescription($offer_id[4]);
    $product->setGender($offer_id[5]);
    $product->setImageLink($offer_id[6]);
    $product->setLink($offer_id[7]);
    $price = new Google_Service_Content_Price();
    $price->setValue($offer_id[8]);
    $price->setCurrency('USD');
    $product->setPrice($price);
    $sprice = new Google_Service_Content_Price();
    if($offer_id[21] != $offer_id[8] && $offer_id[21] > 0) {
	        $sprice->setValue($offer_id[21]);
        	$sprice->setCurrency('USD');
	        $product->setPrice($sprice);
	        $product->setSalePrice($sprice);
    		$product->setCustomLabel1($offer_id[25]);
    	if ((strtotime("+1 year") > strtotime($offer_id[23])) && (strtotime($offer_id[22]) < strtotime($offer_id[23]))) {
        	$product->setSalePriceEffectiveDate($offer_id[22]."/".$offer_id[23]);
	}
    }
    $product->setMpn($offer_id[9]);
    $shipping_weight = new Google_Service_Content_ProductShippingWeight();
    $shipping_weight->setValue(1);
    $shipping_weight->setUnit('pounds');
    $product->setShippingWeight($shipping_weight);
    $product->setColor($offer_id[11]);
    $product->setMaterial($offer_id[12]);
    //$product->setSizes($offer_id[13]);
    $product->setTitle($offer_id[14]);
    $product->setGoogleProductCategory($offer_id[15]);
    //$product->setOfferId($this->buildProductId($offer_id[16]));
    $product->setOfferId($offer_id[16]);
    $product->setAvailability('in stock');
    $product->setItemGroupId($offer_id[18]);
    $product->setProductType($offer_id[19]);
    $product->setContentLanguage(self::CONTENT_LANGUAGE);
    $product->setTargetCountry(self::TARGET_COUNTRY);
    $product->setChannel(self::CHANNEL);
    $product->setDestinations(array(array('destinationName' => 'DisplayAds', 'intention' => 'required')));
    if(!empty($offer_id[26])) {
	    $product->setCustomLabel2($offer_id[26]);
    }
    if(!empty($offer_id[27])) {
	    $product->setCustomLabel3($offer_id[27]);
    }

    return $product;
  }
}
