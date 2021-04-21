<?php

require_once('../../app/Mage.php');

Mage::app();

function getAttributeOptionValue($arg_attribute, $arg_value) {

	$attribute_model = Mage::getModel('eav/entity_attribute');
	$attribute_options_model= Mage::getModel('eav/entity_attribute_source_table') ;
	
	$attribute_code = $attribute_model->getIdByCode('catalog_product', $arg_attribute);
	$attribute = $attribute_model->load($attribute_code);
	
	$attribute_table = $attribute_options_model->setAttribute($attribute);
	$options = $attribute_options_model->getAllOptions(false);
	
	foreach($options as $option) {
		if ($option['label'] == $arg_value) {
			return $option['value'];
		}
	}
	
	return false;
}

function hasRingSize( $product ) {

	foreach ($product->getOptions() as $o)
	{
		if ( strtolower($o->getTitle()) == "ring size" )
			return true;
	}

	return false;

}

$matchingBand = getAttributeOptionValue('matching_band','None');
$chainSize = getAttributeOptionValue('chain_size','None');
$chainLength = getAttributeOptionValue('chain_length','None');
$cutType = getAttributeOptionValue('cut_type','None');
$gemstone = getAttributeOptionValue('gemstone','None');
$stoneAbbrev = getAttributeOptionValue('stone_abbrev','None');
$metalType = getAttributeOptionValue('metal_type','None');
$bandWidth = getAttributeOptionValue('band_width','None');
$color = getAttributeOptionValue('color','None');
$certifiedStone = getAttributeOptionValue('certified_stone','None');
$ringSize = getAttributeOptionValue('ring_size','None');


$products = Mage::getModel('catalog/category')->load()
		->getProductCollection()
		->addAttributeToSelect('*')
		->setOrder('sku', 'ASC')
;


//$_product->setMatchingBand($matchingBand);
//$_product->setChainSize($chainSizeDefault);
//$_product->setChainLength($chainLengthDefault);
//$_product->setCutType($cutType);
//$_product->setColor($color);
//$_product->setGemstone($gemstone);
//$_product->setStoneAbbrev($stoneAbbrev);
//$_product->setCertifiedStone($certifiedStone);
//$_product->setMetalType($metalType);
//$_product->setBandWidth($bandWidth);

foreach($products as $product) {

	$_product = Mage::getModel("catalog/product")->load($product->getId());

	$data = $_product->getData();
	
	$update = false;
	
	$configurable = array();
	
	$columns = array(
			'matching_band',
			'cut_type',
			'color',
			'gemstone',
			'chain_size',
			'chain_length',
			'stone_abbrev',
			'certified_stone',
			'metal_type',
			'band_width'
	);
	
	if ( $_product->getTypeId() == 'configurable' ) {

		if ( hasRingSize($_product) == false ) {
		
			$update = true;
			
			$_product->setRingSize($ringSize);
			
		}
	
		$attributes  = $_product->getTypeInstance(true)->getConfigurableAttributesAsArray($_product);

		foreach( $attributes as $attribute ) {
			$configurable[] = $attribute['attribute_code'];
		}
	}
	
	foreach( $columns as $column ) {

		if ( in_array($column,$configurable) == false ) {
		
			if ( array_key_exists($column,$data) == true && $data[$column] == '' ) {

				echo $column . "\n";
			
				$update = true;

				switch($column) {

					case "matching_band":
						$_product->setMatchingBand($matchingBand);
						break;

					case "chain_size":
						$_product->setChainSize($chainSize);
						break;

					case "chain_length":
						$_product->setChainLength($chainLength);
						break;

					case "cut_type":
						$_product->setCutType($cutType);
						break;

					case "gemstone":
						$_product->setGemstone($gemstone);
						break;

					case "stone_abbrev":
						$_product->setStoneAbbrev($stoneAbbrev);
						break;

					case "metal_type":
						$_product->setMetalType($metalType);
						break;

					case "band_width":
						$_product->setBandWidth($bandWidth);
						break;

					case "color":
						$_product->setColor($color);
						break;

					case "certified_stone":
						$_product->setCertifiedStone($certifiedStone);
						break;
						
					case "ring_size":
						$_product->setRingSize($ringSize);
						break;

				}
			}
		}	
	}
	
	if ( $update ) {
		echo $_product->getSku() . ': updated' . "\n";
		$_product->save();
	} else {
		echo $_product->getSku() . ': ok' . "\n";
	}

}