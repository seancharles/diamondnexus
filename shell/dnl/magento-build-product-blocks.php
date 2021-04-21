<?php

$pid = getmypid();

$root = $_SERVER['HOME'].'html/';

$lock_file = '/tmp/product-blocks.lck';

@mkdir( $root.'var/cache/product' );

if ( file_exists($lock_file) == true ) {
                if(filectime($lock_file) < (time() - (30 * 60))) {
			$fp = fopen($lock_file,'r');
			$pid = fread($fp, filesize($lock_file));
        	        mail(
	                        '2624080870@txt.att.net,'. // Paul B
                        	'2628533318@vtext.com,'. // Bill T
                	        '4146280757@vtext.com,'. // Ed P
        	                '4143059315@vtext.com', // Charles W
                	        'Critical: block builder running for over an hour',
                	        'Critical: block builder running for over an hour'
	                );
			fclose($fp);
			echo "Lock file found. Messaging..."."\n";
			exit;
		} else {
			echo "Lock file found. Closing..."."\n";
			exit;
		}
}

$fp = fopen($lock_file,'w+');
fwrite($fp, $pid);
fclose($fp);

// Load Magento core
$mageFilename = $root.'app/Mage.php';

$i=0;

if (!file_exists($mageFilename)) {
        echo 'SETIError: Could not locate "app" directory or load Magento core files. Please check your script installation and try again.';
        exit;
}

require_once $mageFilename;
Mage::app();

// get layout object
$layout = Mage::getSingleton('core/layout');

//get block object
$block = $layout->createBlock('catalog/product_list');

// get the hlper object
$_helper = $block->helper('catalog/output');

// load the products that are visible
$products = Mage::getModel('catalog/category')->load()
        ->getProductCollection()
        ->addAttributeToSelect('*')
		->addAttributeToFilter('status', 1)
		->addFieldToFilter('visibility',array('neq'=>'1'))
        ->setOrder('sku', 'ASC')
;

$write = Mage::getSingleton('core/resource')->getConnection('core_write');

$_coreHelper = Mage::helper('core');
$_taxHelper  = Mage::helper('tax');

foreach( $products as $_product ) {

	$sku = $_product->getSku();

	$cache_file = $root.'var/cache/product/'.$sku;

    $_id = $_product->getId();

    $_price = $_taxHelper->getPrice($_product, $_product->getPrice());
    $_regularPrice = $_taxHelper->getPrice($_product, $_product->getPrice(), $_simplePricesTax);
    $_finalPrice = $_taxHelper->getPrice($_product, $_product->getFinalPrice());
    $_finalPriceInclTax = $_taxHelper->getPrice($_product, $_product->getFinalPrice(), true);

    $_simplePricesTax = ($_taxHelper->displayPriceIncludingTax() || $_taxHelper->displayBothPrices());
    $_minimalPriceValue = $_product->getMinimalPrice();
    $_minimalPrice = $_taxHelper->getPrice($_product, $_minimalPriceValue, $_simplePricesTax);

	ob_start(); ?>

<a href="<?php echo $_product->getProductUrl() ?>" title="<?php echo preg_replace('/ Engagement Ring/', '', $block->stripTags($block->getImageLabel($_product, 'small_image'), null, true)) ?>" class="product-image">
	<div style="background:url(<?php echo $block->helper('catalog/image')->init($_product, 'small_image')->resize(254); ?>); width:254px; height:254px;" title="<?php echo preg_replace('/ Engagement Ring/', '', $block->stripTags($block->getImageLabel($_product, 'small_image'), null, true)); ?>">
		<?php if ( $_product->getTypeId() == 'simple' && Mage::getModel('cataloginventory/stock_item')->loadByProduct($_product)->getQty() == 0 ) { echo('<img src="'.$block->getSkinUrl('images/catalog/product/listing/SOLD-OUT_254x254.png').'" title="Sold Out!">'); } ?>
	</div>
</a>
<div style="min-height:25px; background:#ffffff; text-align:center;">
	
	<?php $block->getLayout()->getBlock('content'); ?>
	<?php
		if ( $_product->getTypeId() == 'configurable' && substr($_product->getSku(),10,1) != 'B' ) {

			$options = $_product->getTypeInstance(true)->getConfigurableAttributesAsArray($_product);
			
			//echo "<pre>", print_r($options), "</pre>";

			//echo 'Bulding options'."\n";
			
			foreach($options as $option) {

				if ( $option['attribute_id'] == 80 ) {

					foreach( $option['values'] as $value ) {
						echo '<div class="color-swatch '.str_replace('-&-','-',str_replace(' ','-',strtolower($value['label']))).'" title="'.$value['label'].'">&nbsp;</div>';
					}
					
				} else if ( $option['attribute_id'] == 156 ) {

					foreach( $option['values'] as $value ) {
						echo '<div class="cut-swatch '.str_replace(' ','-',strtolower($value['label'])).'" title="'.$value['label'].'">&nbsp;</div>';
					}
				}
			}
			
			
		}
	?>
</div>
<h2 class="product-name"><a href="<?php echo $_product->getProductUrl() ?>" title="<?php echo $block->stripTags(preg_replace('/ Engagement Ring/', '', $_product->getName()), null, true) ?>"><?php echo $_helper->productAttribute($_product, preg_replace('/ Engagement Ring/', '', $_product->getName()), 'name') ?></a></h2>
<?php echo $block->getReviewsSummaryHtml($_product, 'short') ?>
<div class="price-box">
	
    <?php if ($_finalPrice == $_price): ?>
		
		<span class="regular-price" id="product-price-<?php echo $_id ?>">
			<?php echo $_coreHelper->currency($_price,true,true) ?>
		</span>
		
    <?php else: ?>
			
            <p class="old-price">
                <span class="price-label">Original Price:</span>
                <span class="price" id="old-price-<?php echo $_id ?>">
                    <?php echo $_coreHelper->currency($_regularPrice,true,false) ?>
                </span>
            </p>

            <p class="special-price">
                <span class="price-label">Sale Price:</span>
                <span class="price" id="product-price-<?php echo $_id ?>">
                    <?php echo $_coreHelper->currency($_finalPrice,true,false) ?>
                </span>
            </p>
			
    <?php endif; ?>

</div>


<?php   $content = ob_get_contents();

	ob_end_clean();

	//echo 'Finished Building Options'."\n";
	
	$product_count = count($products);
	
	$last_percent = 0;

	if ( md5_file( $cache_file ) != md5($content) ) {

		file_put_contents($cache_file,$content);

		echo $sku."\n";

	} else {
	
		$percent = number_format( $i / $product_count * 100, 2 );

		if ( $percent != $last_percent ) {
			//echo $percent."\n";

			$last_percent = $percent;
		}
	}
	
	$i++;
}

echo "Blocks complete"."\n";

unlink($lock_file);
