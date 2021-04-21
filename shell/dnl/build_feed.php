<?php
	ini_set('display_errors', '1');
	require_once $_SERVER['HOME'].'/html/app/Mage.php';
	require_once $_SERVER['HOME'].'/html/shell/dnl/encoding.php';
	Mage::app();

	// Get the current store id
	$storeId = Mage::app()->getStore()->getId();
	$userModel = Mage::getModel('admin/user');
	$userModel->setUserId(0);
	Mage::getSingleton('admin/session')->setUser($userModel);

	function check_format($text) {
		$encoding = new Encoding;
		return $encoding->fixALL($text);
	}
	function min_display($arrayName) {
		$udisplay = '';
		$udisplay = min(array_unique($arrayName));
		return $udisplay;
	}

	function unique_display($arrayName, $delimter=' / ') {
		$udisplay = '';
		foreach (array_unique($arrayName) as $Name) {
			$udisplay .= $Name.$delimter;
		}
		$udisplay = substr($udisplay,0,-(strlen($delimter)));
		if(strlen($udisplay) == 0) {
			$udisplay = "May Vary";
		}
		return $udisplay;
	}

	function url_exists($url) {
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_NOBODY, true); // set to HEAD request
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // don't output the response
		curl_exec($ch);
		$valid = curl_getinfo($ch, CURLINFO_HTTP_CODE) == 200;
		curl_close($ch);
		return $valid;
	}

	function fileheader() {
		// Header
		return array("adwords_grouping","age_group","brand","condition","description","gender","image_link","link","price","mpn","sale_price","weight","color","material","size","title","google_product_category","id","availability","item_group_id","product_type");
	}

	function getProductList() {
		// Build Profile
		$products = Mage::getModel('catalog/product')->getCollection()
                	->addAttributeToFilter('status', 1)
	                ->addFieldToFilter('visibility',array('eq'=>'4'))
			->setStoreId(1);

		$product_count = count($products);
		return array('count' => $product_count, 'list' => $products);
	}

	function displayProd($productModel) {
		// Get Product
		$product = Mage::getModel('catalog/product')->load($productModel->getId());
		// Get Attributes
                $attributeSetModel = Mage::getModel("eav/entity_attribute_set");
       	        $attributeSetModel->load($product->getAttributeSetId());
               	$attributeSetName  = $attributeSetModel->getAttributeSetName();
		// Get Options? (Ring Size)
		$options_cus = $product->getOptions();

		$pid = $product->getId();
		$sku = $product->getSku();
		$name = $product->getName();
		$status = $product->getStatus();
		$visiblility = $product->getVisibility();
		$url =  $product->getUrlModel()->getUrl($product, array('_ignore_category'=>true));
		//if (url_exists($url) == 0) {
		//	//print "'".$pid."','".$sku."'";
		//	return;
		//}
		$image = $product->getMediaConfig()->getMediaUrl($product->getData('image'));
		//if (url_exists($img) == 0) {
		//	//print "'".$pid."','".$sku."'";
		//	return;
		//}
		$description = check_format($product->getDescription());
		$title = $name;
                //$title = $name." ".$attributeSetName;
		//$MagCateogries = array();
		//foreach ($product->getCategoryIds() as $MagCat) $MagCateogries[] = Mage::getModel('catalog/category')->load($MagCat)->getName();
                $Category = "Apparel &amp;amp;amp; Accessories &amp;amp;gt; Jewelry";
                $ProductType = $Category;

		switch (substr($sku, 0, 1)) {
			case "L":
				$Gender = "Female";
				break;
			case "M":
				$Gender = "Male";
				break;
			default:
				$Gender = "Unisex";
				break;
		}

                switch($attributeSetName) {
			case "Rings":
                        case "Ring":
                        case "Mens Rings":
                        case "Matching Bands":
			case "Pure Carbon Rings":
                                switch (substr($sku, 10, 1)) {
                                        case 'X':
                                                $StoneTypeExtended = " &amp;amp;gt; Diamond Simulants";
                                                break;
                                        case 'B':
                                                $StoneTypeExtended = " &amp;amp;gt; Matching Band";
                                                break;
                                        case 'C':
                                                $StoneTypeExtended = " &amp;amp;gt; Lab Diamonds";
                                                break;
                                        default:
                                                $StoneTypeExtended = "";
                                               break;
                                }
				switch (substr($sku, 4, 2)) {
					case '3S':
					// 3S = "Three Stone";
						$ProductTypeExtended = " &amp;amp;gt; Three Stone";
						break;
					case 'MS':
					// MS = "Multi-stone" 
						$ProductTypeExtended = " &amp;amp;gt;  Multi-stone";
						break;
					case 'OR':
					// OR = "Ornate Styles"
						$ProductTypeExtended = " &amp;amp;gt; Ornate Styles";
						break;
					case 'SA':
					// SA = "Simply Accented Solitaires"
						$ProductTypeExtended = " &amp;amp;gt; Simply Accented Solitaires";
						break;
					case 'SL':
					// SL = "Classic Solitaires"
						$ProductTypeExtended = " &amp;amp;gt; Classic Solitaires";
						break;
					case 'VT':
					// VT = "Vintage"
						$ProductTypeExtended = " &amp;amp;gt; Vintage";
						break;
					default:
						$ProductTypeExtended = "";
						break;
				}
				switch (substr($sku, 0, 4)) {
					case 'LREN':
						$title = $name;
		                         	$Category .= " &amp;amp;gt; Rings";
	        	                        $ProductType .= " &amp;amp;gt; Rings &amp;amp;gt; Engagement".$ProductTypeExtended;
						break;
					default:
						$title = $name;
		                         	$Category .= " &amp;amp;gt; Rings";
                		         	$ProductType .= " &amp;amp;gt; Rings";
						break;
				}
                                break; 
                        case "Bracelets":
                                $Category .= " &amp;amp;gt; Bracelets";
                                $ProductType .= " &amp;amp;gt; Bracelets";
                                break;
                        case "Necklaces":
                                $Category .= " &amp;amp;gt; Necklaces";
                                $ProductType .= " &amp;amp;gt; Necklaces";
                                break;
                        case "Earrings":
                                $Category .= " &amp;amp;gt; Earrings";
                                $ProductType .= " &amp;amp;gt; Earrings";
                                break;
                        case "Pendants":
                                $Category .= " &amp;amp;gt; Charms &amp;amp;amp; Pendants";
                                $ProductType .= " &amp;amp;gt; Charms &amp;amp;amp; Pendants";
                                break;
                        case "Loose Stones":
                                $Category .= " &amp;amp;gt; Precious Stones";
                                $ProductType .= " &amp;amp;gt; Precious Stones";
                                break;
                        case "Watches":
				$Category .= " &amp;amp;gt; Watches";
				$ProductType .= " &amp;amp;gt; Watches";
                                break;
                        default:
				$Category .= "";
				$ProductType .= "";
                                break;
                }

		// Custom Options
		$Sizes = array();
		foreach ($options_cus as $option) {
			switch($option->getTitle()) {
				case 'Ring Size':
				case 'Band Width':
				case 'Chain Length':
				case 'Chain Width':
					foreach ($option->getValues() as $opt) {
						$Sizes[] = $opt->getTitle();
					}
					break;
				default:
					break;
			}
		}
		$size_display = unique_display($Sizes,'-');
		$title = preg_replace('~[[:cntrl:]]~','',preg_replace('/(\s)+/',' ',preg_replace('/s$/','',$title)));

		$Cuts = array();
		$Prices = array();
		$Colors = array();
		$Metals = array();
		$Gemstones = array();
		$SalePrices = array();
		switch($product->getTypeId()) {
			case 'configurable':
				$childProducts = Mage::getModel('catalog/product_type_configurable')->getUsedProducts(null,$product);
				$childs = array();
				foreach ($childProducts as $simpleModel) {
					// Future reference displayProd($simpleModel); will generate the simple
					$_product = Mage::getModel('catalog/product')->load($simpleModel->getId());
					if($_product->getStatus() == 1) {
						$SPrice = $_product->getPrice();
						$SSalePrice = $_product->getFinalPrice();
						$SCut = ($_product->getAttributeText('cut_type') == '') ? "None" : $_product->getAttributeText('cut_type');
						$SColor = ($_product->getAttributeText('color') == '') ? "None" : $_product->getAttributeText('color');
						$SMetal = ($_product->getAttributeText('metal_type') == '') ? "None" : $_product->getAttributeText('metal_type');
						$SGemstone = ($_product->getAttributeText('gemstone') == '') ? "None" : $_product->getAttributeText('gemstone');

						$Cuts[] = $SCut;
						$Prices[] = $SPrice;
						$Colors[] = $SColor;
						$Metals[] = $SMetal;
						$Gemstones[] = $SGemstone;
						$SalePrices[] = $SSalePrice;
						$simpleTitleMods = $SGemstone." ".$SCut." ".$SColor." ".$SMetal;
						$simpleTitle = preg_replace('~[[:cntrl:]]~','',preg_replace('/(\s)+/',' ',preg_replace('/s$/','',preg_replace('/ None/','',$title." ".$simpleTitleMods))));
						$childs[] = array($attributeSetName,"Adult","Diamond Nexus","New",$description,$Gender,$image,$url."?cid=".$_product->getId(),$SPrice,$_product->getSku(),$SSaleprice,"0",$SColor,$SMetal,$size_display,$simpleTitle,$Category,$_product->getId(),"In Stock",$sku,$ProductType);
					}
				}
				$price = min_display($Prices);
				$saleprice = min_display($SalePrices);
				break;
			case 'simple':
				$price = $product->getPrice();
				$saleprice = $product->getFinalPrice();
				$Cuts[] = $product->getAttributeText('cut_type');
				$Colors[] = $product->getAttributeText('color');
				$Metals[] = $product->getAttributeText('metal_type');
				$Gemstones[] = $product->getAttributeText('gemstone');
				$simpleTitleMods = $SGemstone." ".$SCut." ".$SColor." ".$SMetal;
				$title = preg_replace('~[[:cntrl:]]~','',preg_replace('/(\s)+/',' ',preg_replace('/s$/','',preg_replace('/ None/','',$title." ".$simpleTitleMods))));
				break;
			default:
				break;
		}

		$cuts_display = unique_display($Cuts);
		$color_display = unique_display($Colors);
		$metal_display = unique_display($Metals);
		$gems_display = unique_display($Gemstones);

		// Checks
		if((strlen($description) > 2000) || (strlen($description) == 0)) {
			//print "'".$pid."','".$sku."'";
			return;
		} elseif ($price == 0) {
			//print "'".$pid."','".$sku."'";
			return;
		} elseif (strlen($color_display) == 0) {
			//print "'".$pid."','".$sku."'";
			return;
		} elseif (preg_match('/_ignore_category/', $url)) {
			//print "'".$pid."','".$sku."'";
			return;
		} elseif (preg_match('/[0-9].html/', $url) && (!(preg_match('/ring-size/', $url)))) {
			//print "'".$pid."','".$sku."','".$url."'\n";
			return;
		} else {
			//print $sku."\n";
			return array('Result' => array($attributeSetName,"Adult","Diamond Nexus","New",$description,$Gender,$image,$url,$price,$sku,$saleprice,"1",$color_display,$metal_display,$size_display,$title,$Category,$pid,"In Stock",$sku,$ProductType), 'Children' => $childs);
		}
	}

	$listing = getProductList();
	$output = fopen('var/export/base_feed.csv', 'w');
	fputcsv($output,fileheader(),'|','"');
	foreach ($listing['list'] as $product) {
		$result = displayProd($product);
		fputcsv($output,$result['Result'],'|','"');
		foreach ($result['Children'] as $childProduct) {
			fputcsv($output,$childProduct,'|','"');
		}
	}
        // Send Feed
        // diamondnexusbasefeeds FwitiUkX8QgpbwBP6 uploads.google.com
?>
